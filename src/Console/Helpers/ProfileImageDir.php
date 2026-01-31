<?php
declare(strict_types=1);
namespace Console\Helpers;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;

/**
 * Contains functions for creating and deleting the profile images directory.
 */
class ProfileImageDir {
    /**
     * Path to profile images.
     */
    private const IMAGES_PATH = ROOT.DS.'storage'.DS.'app'.DS.'private'.DS .'profile_images';

    /**
     * Performs rmdir operation on the profile images directory.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function rmdirProfileImageDirectories(): int {
        $it = new RecursiveDirectoryIterator(self::IMAGES_PATH, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
                     RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                $response = rmdir($file->getPathname());
            } else {
                $response = unlink($file->getPathname());
            }
        }

        if($response == false) {
            console_error('Failure occurred when deleting images.');
            return Command::FAILURE;
        }
        console_info('All profile images have been deleted.');
        return Command::SUCCESS;
    }
}