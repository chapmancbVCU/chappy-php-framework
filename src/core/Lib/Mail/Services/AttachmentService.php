<?php
namespace Core\Lib\Mail\Services;

use Core\Lib\FileSystem\Uploads;
use Core\Models\EmailAttachments;

class AttachmentService {

    public static function attachmentUpload(EmailAttachments $attachment): ?Uploads {
        if($attachment->isNew()) {
            $uploads = Uploads::handleUpload(
                $_FILES['attachment_name'],
                EmailAttachments::class,
                ROOT.DS,
                '15mb',
                $attachment,
                'attachment_name'
            );
            return $uploads;
        }
        return null;
    }

    public static function deleteAttachment(EmailAttachments $attachment): void {
        if($attachment && file_exists($attachment->path)) {
            unlink($attachment->path);
            $attachment->delete();
        }
    }

    
}