<?php
namespace Core\Models;
use Core\Model;
use Core\Lib\Mail\Attachments;
use App\Models\Users;
use Core\Validators\RequiredValidator as Required;

/**
 * Implements features of the EmailAttachments class.
 */
final class EmailAttachments extends Model {

    // Fields you don't want saved on form submit
    // public const blackList = [];

    // Set to name of database table.
    protected static $_table = 'email_attachments';

    // Soft delete
    protected static $_softDelete = true;
    
    // List your allowed file types.
    protected static $allowedFileTypes;

    // Set your max file size.
    protected static $maxAllowedFileSize = 17825792;

    // Set your file path.  Include your bucket if necessary.
    public static $_uploadPath = 'storage'.DS.'app'.DS.'private'.DS .'email_attachments';
    
    // Fields from your database
    public $attachment_name;
    public $created_at;
    public $deleted = 0;
    public $description;
    public $id;
    public $mime_type;
    public $name;
    public $path;
    public $size;
    public $updated_at;
    public $user_id;

    public function afterDelete(): void {
        // Implement your function
    }

    public function afterSave(): void {
        // Implement your function
    }

    public function beforeDelete(): void {
        // Implement your function
    }

    public function beforeSave(): void {
        $this->timeStamps();
    }

    /**
     * Formats size of attachment to human readable format.
     *
     * @param int $bytes The size of the attachment.
     * @param int $precision The precision in number of decimal places to
     * report.
     * @return string The file size in human readable format.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes === 0) return '0 B';

        $i = (int) floor(log($bytes, 1024));
        $size = $bytes / pow(1024, $i);

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Getter function for $allowedFileTypes array
     *
     * @return array $allowedFileTypes The array of allowed file types.
     */
    public static function getAllowedFileTypes(): array {
        return self::$allowedFileTypes;
    }

    /**
     * Getter function for $maxAllowedFileSize.
     *
     * @return int $maxAllowedFileSize The max file size for an individual 
     * file.
     */
    public static function getMaxAllowedFileSize(): int {
        return self::$maxAllowedFileSize;
    }

    /**
     * Implements onConstruct from parent class.
     *
     * @return void
     */
    public function onConstruct(): void {
        self::$allowedFileTypes = Attachments::getAllowedMimeTypes();
    }

    /**
     * Performs upload
     *
     * @return void
     */
    public static function uploadFile($user_id, $uploads): void {
        // Implement your function
        
    }

    /**
     * Retrieves username for uploader of attachment.
     *
     * @param int $user_id The id for the user.
     * @return string The uploader's username.
     */
    public static function uploadUsername(int $user_id): string {
        $user = Users::findById($user_id);
        return $user->username;
    }

    public function validator(): void {
        $this->runValidation(new Required($this, ['field' => 'description', 'message' => 'Description is required']));
        if($this->isNew()) {
            $this->runValidation(new Required($this, ['field' => 'attachment_name', 'message' => 'You must upload an attachment']));
        }
    }
}
