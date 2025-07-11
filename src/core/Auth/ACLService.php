<?php
declare(strict_types=1);
namespace core\Auth;

use Core\Models\ACL;
use App\Models\Users;
use Core\Lib\Utilities\Arr;

/**
 * Collection of functions for managing User's ACLs.
 */
class ACLService {
    /**
     * Returns an array containing access control list information.  When the 
     * $acl instance variable is empty an empty array is returned.
     *
     * @param Users $user The user whose ACLs we want to use.
     * @return array The array containing access control list information.
     */
    public static function aclsForUser(Users $user) {
        if(empty($user->acl)) return [];
        return json_decode($user->acl, true);
    }
    
    /**
     * Ensures that we are always dealing with an array of ACLs
     *
     * @param mixed $acls An array or any type that we want to add to an array.
     * @return array An array of acls.
     */
    public static function aclToArray(mixed $acls): array {
        if (!is_array($acls)) {
            $acls = [];
        }
        return Arr::map($acls, 'strval');
    }

    /**
     * Add ACL to user's acl field as an element of an array.
     *
     * @param int $user_id The id of the user whose acl field we want to 
     * modify.
     * @param string $acl The name of the new ACL.
     * @return bool True or false depending on success of operation.
     */
    public static function addAcl(int $user_id, string $acl): bool {
        $user = Users::findById($user_id);
        if(!$user) return false;
        $acls = self::aclsForUser($user);
        if(!in_array($acl, $acls)){
            $acls[] = $acl;
            $user->acl = json_encode($acls);
            $user->save();
        }
        return true;
    }

    /**
     * Checks if ACL is found and sets flash message if not the case.  Also, 
     * checks if it is assigned to a user and sets flash message.
     *
     * @param ACL $acl The ACL to verify.
     * @return void
     */
    public static function checkACL(ACL $acl): void {
        if (!$acl) {
            flashMessage('danger', "ACL not found.");
            redirect('admindashboard.manageAcls');
        }
    
        if ($acl->isAssignedToUsers()) {
            flashMessage('danger', "Access denied. '{$acl->acl}' is assigned to one or more users and cannot be edited.");
            redirect('admindashboard.manageAcls');
        }
    }

    /**
     * Deletes ACL if allowed.
     *
     * @param int $id The id for the ACL.
     * @return bool True if deleted, otherwise false.
     */
    public static function deleteIfAllowed(int $id): bool {
        $acl = ACL::findById($id);
        if(!$acl) return false;

        $users = $acl->isAssignedToUsers();
        if(is_countable($users) > 0) {
            flashMessage('info', "Cannot delete ". $acl->acl. ", assigned to one or more users.");
            return false;
        }
        
        $acl->delete();
        flashMessage('success', 'ACL has been deleted');
        return true;
    }

    /**
     * Manages the adding and removing of ACLs.
     *
     * @param array $acls ACLs stored in acl table.
     * @param Users $user The user we want to modify 
     * @param array $newAcls The new ACLs for the user.
     * @param array $userAcls The user's existing ACLs.
     * @return void
     */
    public static function manageAcls(array $acls, Users $user, array $newAcls, array $userAcls): void {
        foreach ($acls as $aclName) {
            $aclKeyStr = (string)$aclName;
            if (in_array($aclKeyStr, $newAcls, true) && !in_array($aclKeyStr, $userAcls, true)) {
                self::addAcl($user->id, $aclKeyStr);
            } elseif (!in_array($aclKeyStr, $newAcls, true) && in_array($aclKeyStr, $userAcls, true)) {
                self::removeAcl($user->id, $aclKeyStr);
            }
        }
    }

    /**
     * Removes ACL from user's acl field array.
     *
     * @param int $user_id The id of the user whose acl field we want to modify.
     * @param string $acl The name of the ACL to be removed.
     * @return bool True if  user is found, otherwise we  return false.
     */
    public static function removeAcl(int $user_id, string $acl): bool {
        $user = Users::findById($user_id);
        if(!$user) return false;
        $acls = self::aclsForUser($user);
        if(in_array($acl,$acls)){
            $key = Arr::search($acls, $acl);
            unset($acls[$key]);
            $user->acl = json_encode($acls);
            $user->save();
        }
        return true;
    }

    /**
     * Sets ACL at registration.  If users table is empty the default 
     * value is Admin.  Otherwise, we set the value to "".
     *
     * @return string The value of the ACL we are setting upon 
     * registration of a user.
     */
    public static function setAclAtRegistration(): string {
        if(Users::findTotal() == 0) {
            return '["Admin"]';
        }
        return '[""]';
    }

    /**
     * Updates user's acl field
     *
     * @param Users $user The user whose ACLs we want to update.
     * @param array $userAcls Existing user's ACLs.
     * @param array $acls All available ACLs.
     * @param array|null $postAcls ACLs from post that are selected.
     * @return void
     */
    public static function updateUserACLs(Users $user, array $userAcls, array $acls, ?array $postAcls = null): void {
        $newAcls = $postAcls ?? [];
        self::manageAcls($acls, $user, $newAcls, $userAcls);
        $user->acl = json_encode($newAcls);
    }

    /**
     * Returns array of unused ACLs.
     *
     * @return array $unUsedAcls An array of unused ACLs.
     */
    public static function unUsedACLs(): array {
        $acls = ACL::getACLs();
        $unUsedAcls = [];
        foreach($acls as $acl) {
            if(!$acl->isAssignedToUsers()) {
                Arr::push($unUsedAcls, $acl);
            }
        }
        return $unUsedAcls;
    }

    /**
     * Returns array of used ACLs.
     *
     * @return array $usedACLs An array of used ACLs.
     */
    public static function usedACLs(): array {
        $acls = ACL::getACLs();
        $usedAcls = [];
        foreach($acls as $acl) {
            if($acl->isAssignedToUsers()) {
                Arr::push($usedAcls, $acl);
            }
        }
        return $usedAcls;
    }

}