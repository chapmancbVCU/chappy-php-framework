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
    public static function deployComposerToggle(): int {
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

        Tools::writeFile(
            CHAPPY_BASE_PATH.DS.'toggle-composer.sh',
            DevStubs::toggleComposer(),
            'toggle-composer.sh'
        );
        return Command::SUCCESS;
    }
}