<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Utilities\Arr;
use Symfony\Component\Mime\Email;
use App\Models\EmailAttachments;

/**
 * Supports attachment processing for MailerService.
 */
class Attachments {
    public const ATTACHMENTS_PATH = CHAPPY_BASE_PATH.DS.'storage'.DS.'app'.DS.'private'.DS.'email_attachments'.DS; 
    
    public const MIME_TYPES = [
        '7z'   => 'application/x-7z-compressed',
        'ai'   => 'application/postscript',
        'psd'  => 'image/vnd.adobe.photoshop',
        'csv'  => 'text/csv',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'gif'  => 'image/gif',
        'gz'   => 'application/gzip',
        'jpg'  => 'image/jpeg',
        'json' => 'application/json',
        'md'   => 'text/markdown',
        'mp3'  => 'audio/mpeg',
        'mp4'  => 'video/mp4',
        'pdf'  => 'application/pdf',
        'png'  => 'image/png',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'rtf'  => 'application/rtf',
        'svg'  => 'image/svg+xml',
        'tar'  => 'application/x-tar',
        'txt'  => 'text/plain',
        'webp' => 'image/webp',
        'xbrl' => 'application/x-xbrl+xml',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml'  => 'application/xml',
        'zip'  => 'application/zip',
    ];

    /**
     * Processes attachment if key labeled content is found.
     *
     * @param array $attachment The attachment.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    protected static function attach(array $attachment, Email $email): Email {
        return $email->attach(
            $attachment['content'],
            $attachment['name'] ?? null,
            $attachment['mime'] ?? null
        );
    }

    /**
     * Processes attachment if key labeled path is found.
     *
     * @param array $attachment The attachment.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    protected static function attachFromPath(array $attachment, Email $email): Email {
        return $email->attachFromPath(
            $attachment['path'],
            $attachment['name'] ?? null,
            $attachment['mime'] ?? null
        );
    }

    /**
     * Used to assemble array for attachment when content key is used.
     *
     * @param EmailAttachment $attachment Instance of the EmailAttachment model.
     * @return array The full path to the file.
     */
    public static function content(EmailAttachments $attachment): array {
        $path = $attachment->path;
    
        if (!file_exists($path)) {
            throw new \RuntimeException("Attachment file not found: {$path}");
        }

        return [
            'content' => file_get_contents($path),
            'name' => $attachment->attachment_name,
            'mime' => $attachment->mime_type
        ];
    }
    
    /**
     * Gets the values from the MIME_TYPES array.
     *
     * @return array The values from the MIME_TYPES array.
     */
    public static function getAllowedMimeTypes(): array {
        return array_values(self::MIME_TYPES);
    }

    /**
     * Returns the MIME type for a given file extension.
     *
     * @param string $ext File extension (e.g., 'pdf', 'jpg', 'docx')
     * @return string The corresponding MIME type or 'application/octet-stream'
     */
    public static function mime(string $ext): string {
        return self::MIME_TYPES[strtolower($ext)] ?? 'application/octet-stream';
    }

    /**
     * Used to assemble array for attachment when path key is used.
     *
     * @param EmailAttachment $attachment Instance of the EmailAttachment model.
     * @return array The data needed to assemble an attachment.
     */
    public static function path(EmailAttachments $attachment): array {
        $path = $attachment->path;
    
        if (!file_exists($path)) {
            throw new \RuntimeException("Attachment file not found: {$path}");
        }

        return [
            'path' => $path,
            'name' => $attachment->attachment_name,
            'mime' => $attachment->mime_type
        ];
    }

    /**
     * Process attachments to be sent.
     *
     * @param array $attachments The array of attachments.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    public static function processAttachments(array $attachments, Email $email): Email {
        if(Arr::isAssoc($attachments)) {
            if(isset($attachments['path'])) {
                $email = self::attachFromPath($attachments, $email);
            } else if(isset($attachments['content'])) {
                $email = self::attach($attachments, $email);
            }
            return $email;
        }

        foreach($attachments as $attachment) {
            if(isset($attachment['path'])) {
                $email = self::attachFromPath($attachment, $email);
            } else if(isset($attachment['content'])) {
                $email = self::attach($attachment, $email);
            }
        }
        return $email;
    }
}