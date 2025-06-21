<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Utilities\Arr;
use Symfony\Component\Mime\Email;

/**
 * Supports attachment processing for MailerService.
 */
class Attachments {
    const MIME_7ZIP = 'application/x-7z-compressed';
    const MIME_BMP = 'image/bmp';
    const MIME_CSS = 'text/css';
    const MIME_CSV = 'text/csv';
    const MIME_DOC = 'application/msword';
    const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const MIME_GIF = 'image/gif';
    const MIME_GZIP = 'application/gzip';
    const MIME_JAVASCRIPT = 'application/javascript';
    const MIME_JPG = 'image/jpeg';
    const MIME_JSON = 'application/json';
    const MIME_MARKDOWN = 'text/markdown';
    const MIME_HTML = 'text/html';
    const MIME_PDF = 'application/pdf';
    const MIME_PHP = 'application/x-httpd-php';
    const MIME_PNG = 'image/png';
    const MIME_SVG = 'image/svg+xml';
    const MIME_TAR = 'application/x-tar';
    const MIME_TEXT = 'text/plain';
    const MIME_WEBP = 'image/webp';
    const MIME_XLS = 'application/vnd.ms-excel';
    const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const MIME_XML = 'application/xml';
    const MIME_ZIP = 'application/zip';

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