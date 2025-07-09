<?php
namespace Core\Lib\Mail\Services;

use Core\Lib\FileSystem\Uploads;
use Core\Models\EmailAttachments;

class AttachmentService {

    /**
     * Generates upload object for attachments.
     *
     * @param EmailAttachments $attachment The attachment to upload.
     * @return Uploads|null The upload object if the attachment is new, 
     * otherwise we return null.
     */
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

    /**
     * Deletes an attachment
     *
     * @param EmailAttachments $attachment The attachment we want to delete 
     * from the filesystem.
     * @return void
     */
    public static function deleteAttachment(EmailAttachments $attachment): void {
        if($attachment && file_exists($attachment->path)) {
            unlink($attachment->path);
            $attachment->delete();
        }
    }
}