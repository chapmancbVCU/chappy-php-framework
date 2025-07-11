<?php
declare(strict_types=1);
namespace Core\Services;

use Core\Input;
use Core\Services\AuthService;
use Core\Lib\Mail\Attachments;
use Core\Lib\FileSystem\Uploads;
use Core\Models\EmailAttachments;
use App\Models\Users;

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

    public static function attachmentUploader($id) {
        return Users::findById((int)$id);
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
     * @return string The attachment's name.
     */
    public static function name(EmailAttachments $attachment): string {
        return ($attachment->isNew()) ? htmlspecialchars($_FILES['attachment_name']['name']) :
            $attachment->attachment_name;
    }

    /**
     * Generates preview for attachment in new tab.
     *
     * @param int $id The id for the attachment's record.
     * @return void
     */
    public static function previewAttachment(int $id) {
        $attachment = EmailAttachments::findById($id);
        if (!$attachment || !file_exists(CHAPPY_BASE_PATH . DS . $attachment->path)) {
            http_response_code(404);
            exit('File not found.');
        }

        header('Content-Type: ' . $attachment->mime_type);
        header('Content-Disposition: inline; filename="' . $attachment->attachment_name . '"');
        readfile($attachment->path);
        exit;
    }

    /**
     * Sets fields for attachment record based on file information and 
     * performs uploads.
     *
     * @param EmailAttachments $attachment The attachment to process and upload.
     * @param Input $request The request for this update or edit.
     * @return void
     */
    public static function processAttachment(EmailAttachments $attachment, Input $request): void {
        $upload = self::attachmentUpload($attachment);
        $attachment->description = $request->get('description');
        $attachment->attachment_name = self::name($attachment);
        $attachment->user_id = AuthService::currentUser()->id;

        // Save record then return if no upload to process when updating record.
        $attachment->save();
        if (!$upload || !$attachment) return;

        // Process attachment, upload, and save record again with remaining fields set.
        $file = $upload->getFiles();
        if(empty($file)) return;

        $file = reset($file);
        if(!$file) return;
        
        $path = EmailAttachments::$_uploadPath . DS;
        $uploadName = $upload->generateUploadFilename($file['name']);
        $attachment->name =$uploadName;
        $attachment->path = $path . $uploadName;
        $attachment->size = $file['size'];
        $attachment->mime_type = Attachments::mime(pathinfo($file['name'], PATHINFO_EXTENSION));
        $upload->upload($path, $uploadName, $file['tmp_name']);
        $attachment->save();
    }
}