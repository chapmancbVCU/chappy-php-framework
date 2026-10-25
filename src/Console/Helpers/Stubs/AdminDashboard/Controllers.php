<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\AdminDashboard;

/**
 * Collection of controller stubs.
 */
class Controllers {
    /**
     * The JS supported version.
     *
     * @return string The contents of the controller class.
     */
    public static function jsController(): string {
        return <<<PHP
<?php
declare(strict_types=1);
namespace App\Controllers;
use Core\Controller;
use Core\Models\ACL;
use App\Models\Users;
use Core\Services\ACLService;
use Core\Models\ProfileImages;
use Core\Services\UserService;
use Core\Models\EmailAttachments;
use Core\Services\DashboardService;
use Core\Services\AttachmentService;
use Core\Lib\Http\JsonResponse;
use Core\Session;
use Throwable;

/**
 * Implements support for our Admindashboard controller.
 */
class AdmindashboardController extends Controller {
    use JsonResponse;

    /**
     * Displays list of attachments.
     *
     * @return void
     */
    public function attachmentsAction(): void {
        \$attachments = EmailAttachments::find();

        foreach(\$attachments as \$attachment) {
            \$attachment->uploadUsername = EmailAttachments::uploadUsername(\$attachment->user_id);
            \$attachment->formattedBytes = EmailAttachments::formatBytes(\$attachment->size);
        }

        \$props = ['attachments' => \$attachments];
        \$this->view->renderJsx(
            viewComponent:'admindashboard.Attachments', 
            props: \$props,
        );
    }

    /**
     * Displays details for a particular E-mail attachment.
     *
     * @param int \$id Primary key for attachment record.
     * @return void
     */
    public function attachmentDetailsAction(int \$id): void {
        \$attachment = EmailAttachments::findById((int)\$id);

        \$props = [
            'attachment' => \$attachment,
            'size' => EmailAttachments::formatBytes(\$attachment->size),
            'uploader' => AttachmentService::attachmentUploader(\$attachment->user_id),
        ];

        \$this->view->renderJsx('admindashboard.AttachmentDetails', \$props);
    }

    /**
     * Deletes ACL from acl table.
     *
     * @param int \$id The id for the ACL we want to delete.
     * @return void
     */
    public function deleteAclAction(int \$id) {
        try {
            if(!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }
            ACLService::deleteIfAllowed(\$id);
            return \$this->jsonResponse(['success' => true]);
        } catch (Throwable \$e) {
            return \$this->jsonError('Server error: ' .  \$e, 500);
        }
    }

    /**
     * Performs delete action on a user.
     *
     * @param int \$id The id for the user we want to delete.
     * @return void
     */
    public function deleteAction(int \$id) {
        try {
            if(!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }
          
            UserService::deleteIfAllowed((int)\$id, false);

            // Respond success and send redirect message.
            return \$this->jsonRedirect('admindashboard.index');
        } catch (Throwable \$e) {
            return \$this->jsonError('Server error: ' .  \$e, 500);
        } 
    }

    /**
     * Deletes an attachment and sets deleted field in table to 1.
     *
     * @param int \$id The primary key for the attachment's database 
     * record.
     * @return void
     */
    public function deleteAttachmentAction(int \$id) {
        try {
            if(!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }
            \$attachment = EmailAttachments::findById(\$id);
            AttachmentService::deleteAttachment(\$attachment);
            return \$this->jsonResponse(['success' => true]);
        } catch (Throwable \$e) {
            return \$this->jsonError('Server error: ' .  \$e, 500);
        }
    }

    /**
     * Deletes an image associated with a user's profile.
     *
     * @return void
     */
    public function deleteImageAction(): void {
        \$resp = ['success' => false];
        if(\$this->request->isPost()) {
            \$resp = UserService::deleteProfileImage(\$this->request);
        }
        \$this->jsonResponse(\$resp);
    }

    /**
     * Presents information about a particular user's profile.
     *
     * @param int \$id The id of the user whose details we want to view.
     * @return void
     */
    public function detailsAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);

        \$props = [
            'profileImage' => ProfileImages::findCurrentProfileImage(\$user->id),
            'user' => \$user
        ];

        \$this->view->renderJsx('admindashboard.Details', \$props);
    }

    /**
     * Supports ability to edit ACLs not assigned to a user through a web form.
     *
     * @param int|string \$id The id of the ACL we want to modify.
     * @return void
     */
    public function editAclAction(int|string \$id): void {
        \$acl = (\$id == 'new') ? new ACL() : ACL::findById((int)\$id);
        ACLService::checkACL(\$acl);
    
        if (\$this->request->isPost()) {
            \$this->request->csrfCheck();
            if(ACLService::saveACL(\$acl, \$this->request)) {
                flashMessage(Session::INFO, "ACL record updated.");
                redirect('admindashboard.manageAcls');
            }
        }

        \$props = [
            'acl' => \$acl,
            'errors' => \$acl->getErrorMessages(),
            'id' => \$id
        ];
        \$this->view->renderJsx('admindashboard.EditACL', \$props);
    }
    
    /**
     * Supports ability for administrators to edit user profiles.
     *
     * @param int \$id The id of the user whose profile we want to modify.
     * @return void
     */
    public function editAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);
    
        // Fetch all available ACLs
        \$acls = ACL::getOptionsForForm();
    
        // Decode stored ACL JSON string into an array
        \$userAcls = ACLService::aclToArray(json_decode(\$user->acl, true));
        \$profileImages = ProfileImages::findByUserId(\$user->id);
    
        if (\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->assign(\$this->request->get(), Users::whiteList, Users::WHITELIST);
    
            // Handle ACL updates from checkboxes
            ACLService::updateUserACLs(\$user, \$userAcls, \$acls, \$_POST['acls']);
            
            if (\$user->save()) {
                ProfileImages::updateSortByUserId(\$user->id, json_decode(\$_POST['images_sorted']));
                redirect('admindashboard.details', [\$user->id]);
            }
        }

        \$props = [
            'acls' => \$acls,
            'errors' => \$user->getErrorMessages(),
            'profileImages' => \$profileImages,
            'user' => \$user,
            'userAcls' => \$user->getAcls()
        ];

        \$this->view->renderJsx('admindashboard.Edit', \$props);
    }

    /**
     * Creates or edits the details of an existing E-mail attachment.
     *
     * @param int|string \$id The primary key for the record associated with an 
     * E-mail attachment.
     * @return void
     */
    public function editAttachmentsAction(int|string \$id): void {
        \$attachment = (\$id == 'new') ? new EmailAttachments() : 
            EmailAttachments::findById((int)\$id);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            AttachmentService::processAttachment(\$attachment, \$this->request);
            if(\$attachment->validationPassed()) {
                redirect('admindashboard.attachments');
            }
        }

        \$props = [
            'attachment' => \$attachment,
            'errors' => \$attachment->getErrorMessages(),
            'id' => \$id
        ];

        \$this->view->renderJsx('admindashboard.AttachmentsForm', \$props);
    }

    /** 
     * The default action for this controller.  It performs rendering of this 
     * site's admin dashboard page.
     * 
     * @return void
     */
    public function indexAction(): void {
        \$list = DashboardService::userList(\$this->request);

        if (\$this->request->isAjax()) {
            // Vary: the same URL also serves HTML, so caches must keep them apart.
            \$this->jsonResponse(['success' => true] + \$list, 200, ['Vary' => 'X-Requested-With']);
            return;
        }

        \$this->view->setSiteTitle('Administration');
        \$this->view->renderJsx('admindashboard.Index', \$list + [
            'aclOptions'    => DashboardService::optionList(ACL::getOptionsForForm()),
            'statusOptions' => DashboardService::optionList(DashboardService::STATUS_OPTIONS),
            'widgetsHtml'   => \$this->view->renderWidgets('dashboard.index', \$this->view->widgets ?? []),
        ]);
    }

    /**
     * Renders view for managing ACLs.
     *
     * @return void
     */
    public function manageACLsAction(): void {
        \$props = [
            'usedAcls' => ACLService::usedACLs(),
            'unUsedAcls' => ACLService::unUsedACLs()
        ];
        \$this->view->renderJsx('admindashboard.ManageAcls', \$props);
    }

    /**
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        \$this->view->setLayout('admin');
    }

    /**
     * Previews an attachment.
     *
     * @param int \$id The primary key for the record of the attachment.
     * @return void
     */
    public function previewAction(int \$id): void {
        AttachmentService::previewAttachment(\$id);
    }

    /**
     * Support ability to toggle on or off the reset password flag for a 
     * particular user.
     *
     * @param int \$id The id of the user whose reset password flag we want to 
     * modify.
     * @return void
     */
    public function setResetPasswordAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->reset_password = (\$this->request->get('reset_password') == 'on') ? 1 : 0;
            if(\$user->save()) {
                redirect('admindashboard.details', [\$user->id]);
            }
        }

        \$props = [
            'checked' => \$user->isResetPWChecked(),
            'errors' => \$user->getErrorMessages(),
            'user' => \$user
        ];
        \$this->view->renderJSX('admindashboard.SetResetPassword', \$props);
    }

    /**
     * Sets active status for a particular user.  The administrator can 
     * toggle the setting on or off using a web form.
     *
     * @param int \$id The id of the user we want to activate or inactivate.
     * @return void
     */
    public function setStatusAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->inactive = (\$this->request->get('inactive') == 'on') ? 1 : 0;
            if(\$user->save()) {
                redirect('admindashboard.details', [\$user->id]);
            }
        }

        \$props = [
            'checked' => \$user->isInactiveChecked(),
            'errors' => \$user->getErrorMessages(),
            'user' => \$user
        ];
        \$this->view->renderJSX('admindashboard.SetAccountStatus', \$props);
    }
}
PHP;
    }

    /**
     * The PHP supported version.
     *
     * @return string The contents of the controller class.
     */
    public static function phpController(): string {
        return <<<PHP
<?php
declare(strict_types=1);
namespace App\Controllers;

use Core\Controller;
use Core\Models\{ACL, EmailAttachments, ProfileImages, Queue, Users};
use Core\Services\{ACLService, AttachmentService, DashboardService, UserService};
use Core\Lib\Pagination\Pagination;
use Core\Lib\Http\JsonResponse;
use Core\Session;

/**
 * Implements support for our Admindashboard controller.
 */
class AdmindashboardController extends Controller {
    use JsonResponse;

    /**
     * Displays list of attachments.
     *
     * @return void
     */
    public function attachmentsAction(): void {
        \$attachments = EmailAttachments::find();
        \$this->view->attachments = \$attachments;
        \$this->view->render('admindashboard.attachments');
    }

    /**
     * Displays details for a particular E-mail attachment.
     *
     * @param int \$id Primary key for attachment record.
     * @return void
     */
    public function attachmentDetailsAction(int \$id): void {
        \$attachment = EmailAttachments::findById((int)\$id);
        \$this->view->uploader = AttachmentService::attachmentUploader(\$attachment->user_id);
        \$this->view->attachment = \$attachment;
        \$this->view->render('admindashboard.attachment_details');
    }

    /**
     * Deletes ACL from acl table.
     *
     * @return void
     */
    public function deleteAclAction(): void {
        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            ACLService::deleteIfAllowed((int)\$this->request->get('id'));
        }
        redirect('admindashboard.manageAcls');
    }

    /**
     * Performs delete action on a user.
     *
     * @param int \$id The id for the user we want to delete.
     * @return void
     */
    public function deleteAction(int \$id): void {
        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            UserService::deleteIfAllowed(\$id, false);
        }
        redirect('admindashboard');
    }

    /**
     * Deletes an attachment and sets deleted field in table to 1.
     *
     * @param int \$id The primary key for the attachment's database 
     * record.
     * @return void
     */
    public function deleteAttachmentAction(int \$id): void {
        \$attachment = EmailAttachments::findById(\$id);
        AttachmentService::deleteAttachment(\$attachment);
        redirect('admindashboard.attachments');
    }

    /**
     * Deletes an image associated with a user's profile.
     *
     * @return void
     */
    public function deleteImageAction(): void {
        \$resp = ['success' => false];
        if(\$this->request->isPost() && \$this->request->csrfCheck()) {
            \$resp = UserService::deleteProfileImage(\$this->request);
        }
        \$this->jsonResponse(\$resp);
    }

    /**
     * Delete a job and redirects back to queueManager.
     *
     * @param int \$id The id of the job to remove from the queue table.
     * @return void
     */
    public function deleteJobAction(int \$id): void {
        \$job = Queue::findById(\$id);
        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$job->delete();
        }
        redirect('admindashboard.queueManager');
    }

    /**
     * Presents information about a particular user's profile.
     *
     * @param int \$id The id of the user whose details we want to view.
     * @return void
     */
    public function detailsAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);
        \$profileImage = ProfileImages::findCurrentProfileImage(\$user->id);
        \$this->view->profileImage = \$profileImage;
        \$this->view->user = \$user;
        \$this->view->render('admindashboard.details');
    }

    /**
     * Supports ability to edit ACLs not assigned to a user through a web form.
     *
     * @param int|string \$id The id of the ACL we want to modify.
     * @return void
     */
    public function editAclAction(int|string \$id): void {
        \$acl = (\$id == 'new') ? new ACL() : ACL::findById((int)\$id);
        ACLService::checkACL(\$acl);
    
        if (\$this->request->isPost()) {
            \$this->request->csrfCheck();
            if(ACLService::saveACL(\$acl, \$this->request)) {
                flashMessage(Session::INFO, "ACL record updated.");
                redirect('admindashboard.manageAcls');
            }
        }
    
        \$this->view->displayErrors = \$acl->getErrorMessages();
        \$this->view->acl = \$acl;
        \$this->view->header = \$acl->isNew() ? "Add ACL" : "Edit ACL";
        \$this->view->render('admindashboard.edit_acl');
    }
    

    /**
     * Supports ability for administrators to edit user profiles.
     *
     * @param int \$id The id of the user whose profile we want to modify.
     * @return void
     */
    public function editAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);
    
        \$this->view->user = \$user;
    
        // Fetch all available ACLs
        \$acls = ACL::getOptionsForForm();
        \$this->view->acls = \$acls;
    
        // Decode stored ACL JSON string into an array
        \$userAcls = ACLService::aclToArray(json_decode(\$user->acl, true));

        // \$this->view->userAcls = Arr::map(\$userAcls, 'strval'); // Ensure values are strings
        \$profileImages = ProfileImages::findByUserId(\$user->id);
    
        if (\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->assign(\$this->request->get(), Users::whiteList, Users::WHITELIST);
    
            // Handle ACL updates from checkboxes
            ACLService::updateUserACLs(\$user, \$userAcls, \$acls, \$_POST['acls']);
            
            if (\$user->save()) {
                ProfileImages::updateSortByUserId(\$user->id, json_decode(\$_POST['images_sorted']));
                redirect('admindashboard.details', [\$user->id]);
            }
        }
    
        \$this->view->profileImages = \$profileImages;
        \$this->view->displayErrors = \$user->getErrorMessages();
        \$this->view->postAction = route('admindashboard.edit', [\$user->id]);
        \$this->view->render('admindashboard.edit');
    }

    /**
     * Creates or edits the details of an existing E-mail attachment.
     *
     * @param int|string \$id The primary key for the record associated with an 
     * E-mail attachment.
     * @return void
     */
    public function editAttachmentsAction(int|string \$id): void {
        \$attachment = (\$id == 'new') ? new EmailAttachments() : 
            EmailAttachments::findById((int)\$id);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            AttachmentService::processAttachment(\$attachment, \$this->request);
            if(\$attachment->validationPassed()) {
                redirect('admindashboard.attachments');
            }
        }

        \$this->view->attachment = \$attachment;
        \$this->view->errors = \$attachment->getErrorMessages();
        \$this->view->uploadMessage = \$attachment->isNew() ? "Upload file" : "Update Attachment";
        \$this->view->header = \$attachment->isNew() ? "Add Attachment" : "Edit Attachment";
        \$this->view->render('admindashboard.attachments_form');
    }

    /** 
     * The default action for this controller.  It performs rendering of this 
     * site's admin dashboard page.
     * 
     * @return void
     */
    public function indexAction(): void {
        \$filter = DashboardService::userFilter(\$this->request);
        \$pagination = Pagination::fromRequest(\$this->request, 10, DashboardService::totalUsers(\$filter))
            ->withFilter(\$filter);
    
        \$this->view->users = DashboardService::paginateUsers(\$pagination, \$filter);
        \$this->view->filter = \$filter;
        \$this->view->pagination = \$pagination;
        \$this->view->aclOptions = ACL::getOptionsForForm();
        \$this->view->statusOptions = DashboardService::STATUS_OPTIONS;
        \$this->view->render('admindashboard.index');
    }

    /**
     * Renders view with information related to a job within the queue table.
     *
     * @param int \$id the id of job in the queue table.
     * @return void
     */
    public function jobDetailsAction(int \$id): void {
        \$job = Queue::findById(\$id);
        \$this->view->payload = json_decode(\$job->payload);
        \$this->view->job = \$job;
        \$this->view->render('admindashboard.job_details');
    }
        
    /**
     * Renders view for managing ACLs.
     *
     * @return void
     */
    public function manageACLsAction(): void {
        \$this->view->usedAcls = ACLService::usedACLs();
        \$this->view->unUsedAcls = ACLService::unUsedACLs();
        \$this->view->render('admindashboard.manage_acls');
    }

    /**
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        \$this->view->setLayout('admin');
    }

    /**
     * Previews an attachment.
     *
     * @param int \$id The primary key for the record of the attachment.
     * @return void
     */
    public function previewAction(int \$id): void {
        AttachmentService::previewAttachment(\$id);
    }

    /**
     * Retrieves list of jobs in queue and renders queue_manager view.
     *
     * @return void
     */
    public function queueManagerAction(): void {
        \$pagination = Pagination::fromRequest(\$this->request, 10, Queue::findTotal());
        \$queue = Queue::find(\$pagination->paginationParams());
        \$this->view->queue = \$queue;
        \$this->view->pagination = \$pagination;
        \$this->view->render('admindashboard.queue_manager');
    }

    /**
     * Support ability to toggle on or off the reset password flag for a 
     * particular user.
     *
     * @param int \$id The id of the user whose reset password flag we want to 
     * modify.
     * @return void
     */
    public function setResetPasswordAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->reset_password = (\$this->request->get('reset_password') == 'on') ? 1 : 0;
            if(\$user->save()) {
                redirect('admindashboard.details', [\$user->id]);
            }
        }

        \$this->view->user = \$user;
        \$this->view->displayErrors = \$user->getErrorMessages();
        \$this->view->postAction = route('admindashboard.setResetPassword', [\$user->id]);
        \$this->view->render('admindashboard.set_reset_password');
    }

    /**
     * Sets active status for a particular user.  The administrator can 
     * toggle the setting on or off using a web form.
     *
     * @param int \$id The id of the user we want to activate or inactivate.
     * @return void
     */
    public function setStatusAction(int \$id): void {
        \$user = Users::findById((int)\$id);
        DashboardService::checkIfCurrentUser(\$user);

        if(\$this->request->isPost()) {
            \$this->request->csrfCheck();
            \$user->inactive = (\$this->request->get('inactive') == 'on') ? 1 : 0;
            if(\$user->save()) {
                redirect('admindashboard.details', [\$user->id]);
            }
        }

        \$this->view->user = \$user;
        \$this->view->displayErrors = \$user->getErrorMessages();
        \$this->view->postAction = route('admindashboard.setStatus', [\$user->id]);
        \$this->view->render('admindashboard.set_account_status');
    }
}
PHP;
    }
}