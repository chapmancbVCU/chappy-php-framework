<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\ProfileImageDir;

/**
 * Run this after performing the migrate:refresh command to delete all 
 * existing profile images.  May need sudo privileges.  
 */
class RemoveProfileImagesCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('tools:rm-profile-images')
            ->setDescription('Removes all profile images.')
            ->setHelp('Might need to use sudo on linux/mac -> sudo php console tools:rm-profile-images.');
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $message = "Are you sure you want to delete all profile images? (y/n)";
        if(ProfileImageDir::confirm($message, $this->question())) {
            return ProfileImageDir::rmdirProfileImageDirectories();
        }
        return self::SUCCESS;
    }
}