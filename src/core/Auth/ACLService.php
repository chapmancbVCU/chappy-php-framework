<?php
namespace core\Auth;

/**
 * Collection of functions for managing User's ACLs.
 */
class ACLService {
    /**
     * Add ACL to user's acl field as an element of an array.
     *
     * @param int $user_id The id of the user whose acl field we want to 
     * modify.
     * @param string $acl The name of the new ACL.
     * @return bool True or false depending on success of operation.
     */
    public static function addAcl($user_id,$acl) {
        $user = self::findById($user_id);
        if(!$user) return false;
        $acls = $user->acls();
        if(!in_array($acl, $acls)){
            $acls[] = $acl;
            $user->acl = json_encode($acls);
            $user->save();
        }
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
     * @return void
     */
    public static function removeAcl($user_id, $acl) {
        $user = self::findById($user_id);
        if(!$user) return false;
        $acls = $user->acls();
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
}