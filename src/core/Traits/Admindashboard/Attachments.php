<?php
declare(strict_types=1);
namespace Core\Traits\Admindashboard;
use Core\Models\EmailAttachments;
use Core\Services\AttachmentService;

/**
 * Trait with predefined actions for admindashboard attachment duties.
 */
trait Attachments {
    /**
     * Displays list of attachments.
     *
     * @return void
     */
    public function attachmentsAction(): void {
        $attachments = EmailAttachments::find();
        $this->view->attachments = $attachments;
        $this->view->render('admindashboard.attachments', true, true);
    }

    /**
     * Displays details for a particular E-mail attachment.
     *
     * @param int $id Primary key for attachment record.
     * @return void
     */
    public function attachmentDetailsAction(int $id): void {
        $attachment = EmailAttachments::findById((int)$id);
        $this->view->uploader = AttachmentService::attachmentUploader($attachment->user_id);
        $this->view->attachment = $attachment;
        $this->view->render('admindashboard.attachment_details', true, true);
    }

    /**
     * Deletes an attachment and sets deleted field in table to 1.
     *
     * @param int $id The primary key for the attachment's database 
     * record.
     * @return void
     */
    public function deleteAttachmentAction(int $id): void {
        $attachment = EmailAttachments::findById($id);
        AttachmentService::deleteAttachment($attachment);
        redirect('admindashboard.attachments');
    }

    /**
     * Creates or edits the details of an existing E-mail attachment.
     *
     * @param int|string $id The primary key for the record associated with an 
     * E-mail attachment.
     * @return void
     */
    public function editAttachmentsAction(int|string $id): void {
        $attachment = ($id == 'new') ? new EmailAttachments() : 
            EmailAttachments::findById((int)$id);

        if($this->request->isPost()) {
            $this->request->csrfCheck();
            AttachmentService::processAttachment($attachment, $this->request);
            if($attachment->validationPassed()) {
                redirect('admindashboard.attachments');
            }
        }

        $this->view->attachment = $attachment;
        $this->view->errors = $attachment->getErrorMessages();
        $this->view->uploadMessage = $attachment->isNew() ? "Upload file" : "Update Attachment";
        $this->view->header = $attachment->isNew() ? "Added Attachment" : "Edit Attachment";
        $this->view->render('admindashboard/attachments_form', true, true);
    }

    /**
     * Previews an attachment
     *
     * @param int $id The primary key for the record of the attachment.
     * @return void
     */
    public function previewAction(int $id): void {
        AttachmentService::previewAttachment($id);
    }
}