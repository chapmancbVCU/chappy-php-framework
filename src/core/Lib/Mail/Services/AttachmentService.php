<?php
namespace Core\Lib\Mail\Services;

use Core\Models\EmailAttachments;

class AttachmentService {


    public static function deleteAttachment(EmailAttachments $attachment): void {
        if($attachment && file_exists($attachment->path)) {
            unlink($attachment->path);
            $attachment->delete();
        }
    }
}