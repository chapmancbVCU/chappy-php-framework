<?php
namespace Core\Models;
use Core\Model;
use Core\Lib\Mail\Attachments;
use App\Models\Users;
use Core\Traits\HasTimestamps;

/**
 * Implements features of the EmailAttachments class.
 */
final class EmailAttachments extends Model {
    use HasTimestamps;
    
    // Fields you don't want saved on form submit
    // public const blackList = [];

    /**
     * Set to name of database table.
     * @var string
     */
    protected static string $_table = 'email_attachments';

    /**
     * Soft delete
     * @var bool
     */
    protected static bool $_softDelete = true;
    
    /**
     * The array of allowed types.
     * @var array
     */
    protected static array $allowedFileTypes;

    /**
     * Columns converted when the model is serialized with toArray() or json_encode().
     * @var array<string, string>
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Set your max file size.
     * @var int
     */
    protected static int $maxAllowedFileSize = 17825792;

    /**
     * Set your file path.  Include your bucket if necessary.
     * @var string
     */
    public static string $_uploadPath = 'storage'.DS.'app'.DS.'private'.DS .'email_attachments';
    
    /**
     * The attachment's name.
     * @var string
     */
    public string $attachment_name;
    
    /**
     * Deleted
     * @var int
     */
    public int $deleted = 0;
    
    /**
     * Description
     * @var string
     */
    public ?string $description = null;

    /**
     * The primary key id
     * @var int
     */
    public int $id;

    /**
     * The mime type.
     * @var string
     */
    public string $mime_type;

    /**
     * Name of the attachment.
     * @var string
     */
    public string $name;

    /**
     * Path to the attachment.
     * @var string
     */
    public string $path;

    /**
     * Size of the attachment.
     * @var int
     */
    public int $size;

    /**
     * ID of the recipient.
     * @var int
     */
    public int $user_id;

    /**
     * Implementation of beforeSave function where timestamps are updated.
     *
     * @return void
     */
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
        $this->runValidation($this->required()->fieldName('description')->validate($this->description));
        if($this->isNew()) {
            $this->runValidation($this->required()->fieldName('attachment_name')->validate($this->attachment_name));
        }
    }
}
