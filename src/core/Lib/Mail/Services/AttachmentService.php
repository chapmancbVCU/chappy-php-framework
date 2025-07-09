<?php
namespace Core\Lib\Mail\Services;

use Core\Lib\Mail\Attachments;
use Core\Lib\FileSystem\Uploads;
use Core\Models\EmailAttachments;

/**
 * Service for managing attachment uploads and deletion.
 */
final class AttachmentService {

    /**
     * Generates upload object for attachments.
     *
     * @param EmailAttachments $attachment The attachment to upload.
     * @return Uploads|null The upload object if the attachment is new, 
     * otherwise we return null.
     */
    public static function attachmentUpload(EmailAttachments $attachment): ?Uploads {
        if($attachment->isNew()) {
            $upload = Uploads::handleUpload(
                $_FILES['attachment_name'],
                EmailAttachments::class,
                ROOT.DS,
                '15mb',
                $attachment,
                'attachment_name'
            );
            return $upload;
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

    /**
     * Sets name for the attachment.
     *
     * @param EmailAttachments $attachment The attachment whose name is being 
     * set.
     * @param string|null $attachmentName The name from POST.
     * @return string The attachment's name.
     */
    public static function name(EmailAttachments $attachment, ?string $attachmentName = null): string {
        return ($attachment->isNew()) ? htmlspecialchars($attachmentName) :
            $attachment->attachment_name;
    }

    /**
     * Sets fields for attachment record based on file information and 
     * performs uploads.
     *
     * @param EmailAttachments $attachment The attachment to process and upload.
     * @param Uploads|null $upload The handler for the upload of this attachment.
     * @return void
     */
    public static function processAttachment(EmailAttachments $attachment, ?Uploads $upload = null): void {
        if($upload) {
            $file = $upload->getFiles();
            $path = EmailAttachments::$_uploadPath . DS;
            $uploadName = $upload->generateUploadFilename($file[0]['name']);
            $attachment->name =$uploadName;
            $attachment->path = $path . $uploadName;
            $attachment->size = $file[0]['size'];
            $attachment->mime_type = Attachments::mime(pathinfo($file[0]['name'], PATHINFO_EXTENSION));
            $upload->upload($path, $uploadName, $file[0]['tmp_name']);
            $attachment->save();
        }
    }
}