<?php
namespace Core\Models;

use Core\Model;
use Core\Traits\HasTimestamps;

/**
 * Describes ACL model.
 */
final class ACL extends Model {
    use HasTimestamps;
    
    /**
     * The acl value.
     * @var string
     */
    public ?string $acl = null;

    /** 
     * The blacklist array. 
     * @var array
     */
    public const blackList = ['id', 'deleted'];

    /** 
     * Deleted 
     * @var int
     */
    public int $deleted = 0;

    /**
     * The current id.
     * @var int
     */

    public int $id;

    /** 
     * $_softDelete value. 
     * @var bool
     */
    protected static bool $_softDelete = true;

    /**
     * The name of the table.
     * @var string
     */
    protected static string $_table = 'acl';

    /**
     * The whitelist for the class.
     */
    public const whiteList = ['acl'];

    /**
     * Implements beforeSave function described in Model parent class.  
     * Ensures timestamps are created and updated.
     *
     * @return void
     */
    public function beforeSave(): void {
        if ($this->isAssignedToUsers()) {
            $this->addErrorMessage("Cannot update '{$this->acl}', assigned to one or more users.");
        }
        $this->timeStamps();
    }

    /**
     * Generates list of ACL options based on ACL table.  Compatible with 
     * dropdown and checkbox group elements.
     *
     * @return array $aclArray The array containing ACLs from ACL table.
     */
    public static function getOptionsForForm(): array {
        return self::toOptions('acl', 'acl', ['order' => 'acl']);
    }

    /**
     * Retrieves list of all ACLs sorted by the acl field.
     *
     * @return array The list of ACLs that is returned from the database.
     */
    public static function getACLs(): array {
        return self::find(['order' => 'acl']);
    }

    /**
     * Check if ACL is assigned to any users
     *
     * @return bool True if is in use.  Otherwise, we return false.
     */
    public function isAssignedToUsers(): bool {

        if($this->isNew()) return false;
        return self::getDb()->valueExistsInColumn('users', 'acl', $this->acl);
    }

    /**
     * Ensures fields are required and unique.
     *
     * @return void
     */
    public function validator(): void {
        $this->runValidation('acl', ['required']);
        if($this->isNew()) {
            $this->runValidation('acl', ['unique:'.self::class]);
        }
    }
}