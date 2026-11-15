<?php
namespace Console\Helpers;
use Console\Console;
use Console\Helpers\Stubs\Dev\DevStubs;
use Symfony\Component\Console\Command\Command;

/**
 * Supports ability to deploy framework dev tools.
 */
class DevTools extends Console {
    /**
     * Generates template files and script for toggling composer config 
     * between local and remote.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function composerFiles(): int {
        Tools::writeFile(
            CHAPPY_BASE_PATH.DS.'composer_local.txt',
            DevStubs::composerLocal(),
            'composer_local.txt'
        );

        Tools::writeFile(
            CHAPPY_BASE_PATH.DS.'composer_remote.txt',
            DevStubs::composerRemote(),
            'composer_remote.txt'
        );

        
        return Command::SUCCESS;
    }

    public static function toggle(): int {
        return Tools::writeFile(
            CHAPPY_BASE_PATH.DS.'toggle-composer.sh',
            DevStubs::toggleComposer(),
            'toggle-composer.sh'
        );
    }

    public static function toggleWin(): int {
        return Tools::writeFile(
            CHAPPY_BASE_PATH.DS.'toggle-composer.bat',
            DevStubs::toggleComposerWin(),
            'toggle-composer.bat'
        );
    }
}