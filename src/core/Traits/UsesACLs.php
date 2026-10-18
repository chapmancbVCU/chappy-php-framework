<?php
declare(strict_types=1);

namespace Core\Traits;

/**
 * ACL specific items needed for the user models.
 */
trait UsesACLs {
    public string $acl;

    /**
     * Returns the user's assigned ACLs as an array.
     *
     * Decodes the JSON-encoded acl field. Returns an empty array if the field
     * is null, empty, or not valid JSON — so callers always get an array.
     *
     * @return array The user's ACLs, or an empty array if none/invalid.
     */
    public function getAcls(): array {
        $userAcls = json_decode($this->acl, true);
        return is_array($userAcls) ? $userAcls : [];
    }
    
    /**
     * Checks if the user has a specific ACL assigned.
     *
     * @param string $acl The ACL to check.
     * @return bool True if the user has the ACL, otherwise false.
     */
    public function hasAcl(string $acl): bool {
        $userAcls = json_decode($this->acl, true);
    
        if (!is_array($userAcls)) {
            return false; // Ensures it always returns a boolean
        }
        return in_array($acl, $userAcls, true);
    }
}