<?php
declare(strict_types=1);
namespace Core\Lib\FileSystem;
use Core\Model;
use InvalidArgumentException;

/**
 * Provides support for file uploads.
 */
class Uploads {
    /**
     * Path to the bucket where file are stored.
     * @var string
     */
    private string $_bucket;

    /**
     * An array of allowed file types.
     * @var array
     */
    protected array $_allowedFileTypes = [];

    /**
     * An array of error objects.
     * @var array
     */
    private array $_errors = [];

    /**
     * The name of the field associated with file upload.
     * @var string
     */
    protected string $_fieldName;

    /**
     * An array containing objects with information about files.
     * @var array
     */
    protected array $_files= []; 

    /**
     * The maximum allowed upload size.
     * @var integer
     */
    protected int $_maxAllowedSize;

    /** Constant for specifying multiple file upload. */
    const MULTIPLE = 'multiple';
    /** Constant for specifying single file upload. */
    const SINGLE = 'single';

    /**
     * Message describing maximum allowable size.
     * @var string
     */
    protected string $sizeMsg;

    /**
     * Creates instance of Uploads class.
     *
     * @param array|string $files Array of files to be uploaded.
     * @param array $imageTypes An array containing a list of acceptable file 
     * types for a particular upload action.
     * @param int $maxAllowedSize Maximum allowable size for a particular 
     * file.  This can vary depending on requirements.
     * @param string $bucket The location where the files will be stored.
     * @param string $sizeMsg The message describing the maximum allowable 
     * size usually described as <size_as_an_int><bytes|mb|gb> (e.g.: 5mb).
     * @param string $mode A constant to set whether or not we are 
     * working with a single file upload or an array regarding form setup.
     */
    public function __construct(
        array|string $files, 
        array $fileTypes, 
        int $maxAllowedSize, 
        string $bucket, 
        string $sizeMsg, 
        string $mode
    ) {
        if (!in_array($mode, [self::SINGLE, self::MULTIPLE], true)) {
            throw new InvalidArgumentException("Invalid upload mode: $mode");
        }

        $this->_files = self::restructureFiles($files, $mode);
        $this->_allowedFileTypes = $fileTypes;
        $this->_maxAllowedSize = $maxAllowedSize;
        $this->_bucket = $bucket;
        $this->sizeMsg = $sizeMsg;
    }
    
    /**
     * Adds an error message to the $_errors array.
     *
     * @param string $name The name of the error.
     * @param string $message The message associated with this error.
     * @return void
     */
    protected function addErrorMessage(string $name, string $message): void {
        error("Upload error: $message"); // Log validation errors
        if (!isset($this->_errors[$name])) {
            $this->_errors[$name] = [];
        }
        $this->_errors[$name][] = $message;
    }

    /**
     * Processes list of errors associated with uploads and makes them 
     * presentable to user during validation.
     *
     * @param bool|array $errors The errors, if any are detected will be an array.
     * @param Model $model The model associated with the errors.
     * @param string $name The name of the field in the model for the errors.
     * @return void
     */
    public function errorReporting(bool|array $errors, Model $model, string $name): void {
        if (is_array($errors)) {
            foreach ($errors as $field => $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $msg) {
                        $model->addErrorMessage($field, $msg);
                    }
                } else {
                    $model->addErrorMessage($field, $messages);
                }
            }
        }
    }

    /**
     * Generates a unique filename for an uploaded file while preserving its extension.
     *
     * Uses a cryptographically secure random hash to create a unique base name.
     * Falls back to 'bin' if no extension is found in the original filename.
     *
     * @param string $originalFilename The original filename (used to extract the extension).
     * @return string A unique, safely generated filename with the original extension.
     *
     * @throws \Exception If it was not possible to gather sufficient entropy (from random_bytes).
     */
    public function generateUploadFilename($originalFilename): string {
        // Generate extension and fallback if no extension
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'bin';

        $hash = bin2hex(random_bytes(16)); // 32-character hash
        return $hash . '.' . $extension;
    }

    /**
     * Getter function for the $_files array.
     *
     * @return array The $_files array.
     */
    public function getFiles(): array {
        return $this->_files;
    }

    /**
     * Handles file uploads and returns an Uploads instance if valid.
     * 
     * @param array $file The file input from $_FILES.
     * @param string $uploadModel The name of the model class responsible for uploads.
     * @param string $bucket Upload destination.
     * @param string $sizeMsg Size description for error messages.
     * @param Model $model The associated with the view you are working with.  
     * May or may not be same as $uploadModel if $uploadModel has index 
     * id field associated with another model.
     * @param string $name The name of the field for upload from form.
     * @param string $mode Use Uploads::SINGLE for single file uploads or 
     * Uploads::MULTIPLE for multiple file uploads.
     * @return Uploads|null Returns Uploads instance if valid, otherwise null.
     */
    public static function handleUpload(
        array $file, 
        string $uploadModel, 
        string $bucket, 
        string $sizeMsg, 
        Model $model,
        string $name,
        string $mode = self::SINGLE
    ): ?self {

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        
        // Ensure the model class exists and has required methods
        if (!class_exists($uploadModel) || !method_exists($uploadModel, 'getAllowedFileTypes') || 
                !method_exists($uploadModel, 'getMaxAllowedFileSize')) {
            throw new InvalidArgumentException("Invalid model class: $uploadModel");
        }

        // Create an instance of Uploads
        $uploadInstance = new static(
            $file, 
            $uploadModel::getAllowedFileTypes(), 
            $uploadModel::getMaxAllowedFileSize(), 
            $bucket, 
            $sizeMsg, 
            $mode
        );

        $uploadInstance->_fieldName = $name;

        // Run validation and report if any errors.
        $uploadInstance->runValidation();
        $uploadInstance->errorReporting($uploadInstance->validates(), $model, $name);

        return $uploadInstance;
    }

    /**
     * Restructures $_FILES data based on mode.
     *
     * @param array $files The uploaded file(s).
     * @param string $mode Upload mode: Uploads::SINGLE or Uploads::MULTIPLE.
     * @return array Structured file array.
     */
    public static function restructureFiles(array $files, string $mode = self::SINGLE): array {
        $structured = [];

        if ($mode === self::MULTIPLE) {
            foreach ($files['tmp_name'] as $key => $val) {
                $structured[] = [
                    'tmp_name' => $files['tmp_name'][$key],
                    'name'     => $files['name'][$key],
                    'size'     => $files['size'][$key],
                    'error'    => $files['error'][$key],
                    'type'     => $files['type'][$key],
                ];
            }
        } else {
            $structured[] = [
                'tmp_name' => $files['tmp_name'],
                'name'     => $files['name'],
                'size'     => $files['size'],
                'error'    => $files['error'],
                'type'     => $files['type'],
            ];
        }

        return $structured;
    }


    /**
     * Performs validation tasks.
     *
     * @return void
     */
    public function runValidation(): void { 
        if (empty($this->_files) || !isset($this->_files[0]['error'])) {
            return; // No file, do nothing
        }
    
        $firstFile = $this->_files[0];
    
        // ✅ Allow user to skip uploading a file without error
        if ($firstFile['error'] === UPLOAD_ERR_NO_FILE) {
            info("No file uploaded. Skipping upload validation.");
            return;
        }
    
        if ($firstFile['error'] === UPLOAD_ERR_INI_SIZE) {
            $this->addErrorMessage($this->_fieldName, "The file exceeds the maximum upload size allowed by the server.");
            return;
        }
    
        if ($firstFile['error'] !== UPLOAD_ERR_OK) {
            $this->addErrorMessage($this->_fieldName, "File upload failed with PHP error code {$firstFile['error']}.");
            return;
        }
    
        $this->validateSize();
        $this->validateFileType();
    }
    

    /**
     * Performs file upload.
     *
     * @param string $path Directory where file will exist when uploaded.
     * @param string $uploadName The actual name for the file when uploaded.
     * @param string $fileName The temporary file name.
     * @return void
     */
    public function upload($path, $uploadName, $fileName): void {
        info("Attempting to upload file: $uploadName | Path: $path");
        if (!file_exists($path)) {
            mkdir($path);
        }
        
        $destination = $this->_bucket.$path.$uploadName;
        if(move_uploaded_file($fileName, $destination)) {
            info("File uploaded successfully: $uploadName | Destination: $destination");
        } else {
            error("File upload failed: Could not move $uploadName to $destination");
        }
    }

    /**
     * Reports on success of validation.
     *
     * @return bool|array True if validation is successful.  Otherwise,
     * we return the $_errors array.
     */
    public function validates() {
        return (empty($this->_errors)) ? true : $this->_errors;
    }

    /**
     * Validates file type and sets error message if file type is invalid.
     *
     * @return void
     */
    protected function validateFileType(): void { 
        $reportTypes = [];
    
        // Normalize allowed file types to MIME strings
        foreach ($this->_allowedFileTypes as $type) {
            $reportTypes[] = is_int($type)
                ? image_type_to_mime_type($type)
                : $type;
        }
    
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach ($this->_files as $file) {
            $filePath = $file['tmp_name'];
            $fileName = $file['name'];
    
            // ✅ Skip empty file slots (e.g. when no file was uploaded)
            if (empty($filePath)) {
                debug("Skipping empty file slot for: $fileName");
                continue;
            }
    
            // Get the MIME type of the file
            $mimeType = $finfo->file($filePath) ?: '';
    
            // Check if the file type is allowed
            if (!in_array($mimeType, $this->_allowedFileTypes, true)) {
                $msg = "$fileName is not an allowed file type. Please use the following types: " . implode(', ', $reportTypes);
                $this->addErrorMessage($this->_fieldName, $msg);
            }
        }
    }
    
    /**
     * Validates file size and sets error message if file is too large.
     *
     * @return void
     */
    protected function validateSize(): void {
        foreach ($this->_files as $file) {
            $name = $file['name'] ?? 'Unknown file';
    
            if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                continue;
            }
    
            if ($file['error'] !== UPLOAD_ERR_OK) {
                // optionally log or skip error files
                warning("Skipping file {$name} due to upload error code {$file['error']}");
                continue;
            }
    
            if (!isset($file['size']) || !is_numeric($file['size'])) {
                warning("Skipping file {$name}: invalid size value.");
                continue;
            }
    
            debug("Checking size of file {$name}: {$file['size']} bytes (limit: {$this->_maxAllowedSize})");
    
            if ($file['size'] > $this->_maxAllowedSize) {
                $msg = "{$name} is over the max allowed size of " . $this->sizeMsg . ".";
                $this->addErrorMessage($this->_fieldName, $msg);
            }
        }
    }
    
    
}