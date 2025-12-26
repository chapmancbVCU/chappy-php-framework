<?php
declare(strict_types=1);
namespace Core\Lib\FileSystem;
use Core\Model;
use Core\Lib\Logging\Logger;
use InvalidArgumentException;
/**
 * Provides support for file uploads.
 */
class Uploads {
    private string $_bucket;
    protected array $_allowedFileTypes = [];
    private array $_errors = [];
    protected string $_fieldName;
    protected array $_files= []; 
    protected int $_maxAllowedSize;
    const MULTIPLE = 'multiple';
    const SINGLE = 'single';
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
        Logger::log("Upload error: $message", Logger::ERROR); // Log validation errors
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
            Logger::log("No file uploaded. Skipping upload validation.", Logger::INFO);
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
        Logger::log("Attempting to upload file: $uploadName | Path: $path", Logger::INFO);
        if (!file_exists($path)) {
            mkdir($path);
        }
        
        $destination = $this->_bucket.$path.$uploadName;
        if(move_uploaded_file($fileName, $destination)) {
            Logger::log("File uploaded successfully: $uploadName | Destination: $destination", Logger::INFO);
        } else {
            Logger::log("File upload failed: Could not move $uploadName to $destination", Logger::ERROR);
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
    
        foreach ($this->_files as $file) {
            $filePath = $file['tmp_name'];
            $fileName = $file['name'];
    
            // ✅ Skip empty file slots (e.g. when no file was uploaded)
            if (empty($filePath)) {
                Logger::log("Skipping empty file slot for: $fileName", Logger::WARNING);
                continue;
            }
    
            // Get the MIME type of the file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
    
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
                Logger::log("Skipping file {$name} due to upload error code {$file['error']}", Logger::WARNING);
                continue;
            }
    
            if (!isset($file['size']) || !is_numeric($file['size'])) {
                Logger::log("Skipping file {$name}: invalid size value.", Logger::WARNING);
                continue;
            }
    
            Logger::log("Checking size of file {$name}: {$file['size']} bytes (limit: {$this->_maxAllowedSize})", Logger::DEBUG);
    
            if ($file['size'] > $this->_maxAllowedSize) {
                $msg = "{$name} is over the max allowed size of " . $this->sizeMsg . ".";
                $this->addErrorMessage($this->_fieldName, $msg);
            }
        }
    }
    
    
}