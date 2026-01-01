<?php
declare(strict_types=1);
namespace Core\Traits\Admindashboard;
use Core\Services\ACLService;
use Core\Models\ACL;
use Core\Session;

trait ACLs {
    /**
     * Deletes ACL from acl table.
     *
     * @param int $id The id for the ACL we want to delete.
     * @return void
     */
    public function deleteAclAction(): void {
        if($this->request->isPost()) {
            $this->request->csrfCheck();
            ACLService::deleteIfAllowed($this->request->get('id'));
        }
        redirect('admindashboard.manageAcls');
    }

    /**
     * Supports ability to edit ACLs not assigned to a user through a web form.
     *
     * @param int|string $id The id of the ACL we want to modify.
     * @return void
     */
    public function editAclAction(int|string $id): void {
        $acl = ($id == 'new') ? new ACL() : ACL::findById((int)$id);
        ACLService::checkACL($acl);
    
        if ($this->request->isPost()) {
            $this->request->csrfCheck();
            if(ACLService::saveACL($acl, $this->request)) {
                flashMessage(Session::INFO, "ACL record updated.");
                redirect('admindashboard.manageAcls');
            }
        }
    
        $this->view->displayErrors = $acl->getErrorMessages();
        $this->view->acl = $acl;
        $this->view->header = $acl->isNew() ? "Added ACL" : "Edit ACL";
        $this->view->render('admindashboard.edit_acl', true, true);
    }

    /**
     * Renders view for managing ACLs.
     *
     * @return void
     */
    public function manageACLsAction(): void {
        $this->view->usedAcls = ACLService::usedACLs();
        $this->view->unUsedAcls = ACLService::unUsedACLs();
        $this->view->render('admindashboard.manage_acls', true, true);
    }
}