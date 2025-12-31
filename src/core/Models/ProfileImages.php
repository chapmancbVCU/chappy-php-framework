<?php
declare(strict_types=1);
namespace Core\Models;
use Core\Model;
use Core\Lib\Utilities\Arr;
use Core\Lib\FileSystem\Uploads;

/**
 * Supports CRUD operations on profile image records.
 */
final class ProfileImages extends Model {
    /**
     * The allowed file types.
     * @var array
     */
    protected static $allowedFileTypes = ['image/gif', 'image/jpeg', 'image/png'];

    /**
     * Deleted value, defaults to 0.
     * @var int
     */
    public $deleted = 0;

    /**
     * ID for this image.
     * @var int
     */
    public $id;

    /**
     * Max allowed size.
     * @var int
     */
    protected static $maxAllowedFileSize = 5242880;

    /**
     * Name of the file.
     * @var string
     */
    public $name;

    /**
     * Soft delete mode set to true.
     * @var bool
     */
    protected static $_softDelete = true;

    /**
     * Sorted position.
     * @var int
     */
    public $sort;

    /**
     * Name of the table.
     * @var string
     */
    protected static $_table = 'profile_images';

    /**
     * Upload path.
     * @var string
     */
    protected static $_uploadPath = 'storage'.DS.'app'.DS.'private'.DS .'profile_images'.DS.'user_';

    /**
     * The URL for the profile image.
     *
     * @var string
     */
    public $url;

    /**
     * ID of user associated with image.
     *
     * @var int
     */
    public $user_id;

    /**
     * Implements beforeSave function described in Model parent class. 
     * Generates timestamps.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
    }
    
    /**
     * Deletes a profile image by id.
     *
     * @param int $id The id of the image we want to delete.
     * @return bool Result of delete operation.  True if success, otherwise 
     * false.
     */
    public static function deleteById(int $id): bool {
        $image = self::findById($id);
        $deleted = false;
        if($image) {
            $user_id = $image->user_id;
            unlink(ROOT.DS.self::$_uploadPath.$image->user_id.DS.$image->name);
            $deleted = $image->delete();
            if($deleted) {
                self::updateSortByUserId($user_id);
            }
        }
        return $deleted;
    }
    
    /**
     * Sets delete field in profile_images table to 1 when user is deleted 
     * from users table.  Accepts parameter for toggling on or off unlink 
     * to permanently image file. 
     *
     * @param int $user_id The user id for user whose image we want to delete.
     * @param bool $unlink Set to true if you want to unlink or false to 
     * preserve image in storage.
     * @return void
     */
    public static function deleteImages(int $user_id, bool $unlink = false): void {
        $images = self::find([
            'conditions' => 'user_id = ?',
            'bind' => [$user_id]
        ]);
        foreach($images as $image) {
            $image->delete();
        }
        if($unlink) {
            $dirName = ROOT.DS.self::$_uploadPath.$image->user_id;
            array_map('unlink', glob("$dirName/*.*"));
            rmdir($dirName);
            unlink($dirName.DS);
        }
    }

    /**
     * Returns currently set profile image for a user.
     *
     * @param int $user_id The id of the user whose profile image we want to 
     * retrieve.
     * @return ProfileImages|bool The model for profile images if images 
     * exits, otherwise we return false.
     */
    public static function findCurrentProfileImage(int $user_id): ProfileImages|bool {
        return self::findFirst([
            'conditions' => 'user_id = ? AND sort = 0',
            'bind' => ['user_id' => $user_id]
        ]);
    }

    /**
     * Finds all profile images for a user.
     *
     * @param int $user_id The id of the user whose profile images we want to 
     * retrieve.
     * @return bool|array The associative array of profile image records for a 
     * user.
     */
    public static function findByUserId(int $user_id): bool|array {
        return self::find([
            'conditions' => 'user_id = ?',
            'bind' => ['user_id' => $user_id],
            'order' => 'sort'
        ]);
    }

    /**
     * Getter function for $allowedFileTypes array
     *
     * @return array $allowedFileTypes The array of allowed file types.
     */
    public static function getAllowedFileTypes(): array {
        return self::$allowedFileTypes;
    }

    /**
     * Getter function for $maxAllowedFileSize.
     *
     * @return int $maxAllowedFileSize The max file size for an individual 
     * file.
     */
    public static function getMaxAllowedFileSize(): int {
        return self::$maxAllowedFileSize;
    }
    
    /**
     * Updates sort order by user id.
     *
     * @param int $user_id The id of the user whose profile images we want 
     * to sort.
     * @param array $sortOrder An array containing sort values for a profile 
     * image.
     * @return void
     */
    public static function updateSortByUserId(int $user_id, array $sortOrder = []): void {
        $images = self::findByUserId($user_id);
        $i = 0;
        foreach($images as $image) {
            $val = 'image_'.$image->id;
            $sort = (Arr::contains($sortOrder, $val)) ? Arr::search($sortOrder, $val) : $i;
            $image->sort = $sort;
            $image->save();
            $i++;
        }
    }

    /**
     * Performs upload operation for a profile image.
     *
     * @param int $user_id The id of the user that the upload operation 
     * is performed upon.
     * @param Uploads $uploads The instance of the Uploads class for this 
     * upload.
     * @return void
     */
    public static function uploadProfileImage(int $user_id, Uploads $uploads): void {
        $lastImage = self::findFirst([
            'conditions' => "user_id = ?",
            'bind' => [$user_id],
            'order' => 'sort DESC'
        ]);
        $lastSort = (!$lastImage) ? 0 : $lastImage->sort;
        $path = self::$_uploadPath.$user_id.DS;
        foreach($uploads->getFiles() as $file) {
            $uploadName = $uploads->generateUploadFilename($file['name']);
            $image = new self();
            $image->url = $path . $uploadName;
            $image->name = $uploadName;
            $image->user_id = $user_id;
            $image->sort = $lastSort;
            if($image->save()) {
                $uploads->upload($path, $uploadName, $file['tmp_name']);
                $lastSort++;
            }
        }
    }
}