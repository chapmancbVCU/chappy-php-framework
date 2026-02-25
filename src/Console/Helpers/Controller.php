<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports operations related to generating controllers.
 */
final class Controller {
    /**
     * Sets layout for controller
     *
     * @param InputInterface $input
     * @return string|int The layout or Command::FAILURE if there is an issue.
     */
    public static function layout(InputInterface $input): string|int {
        $layoutInput = $input->getOption('layout');
        if($layoutInput === false) {
            $layout = 'default';
        } else if ($layoutInput === null) {
            console_warning('Please supply name of layout.');
            return Command::FAILURE;
        } else {
            if($layoutInput === '') {
                console_warning('Please supply name of layout.');
                return Command::FAILURE;
            }
            $layout = Str::lower($layoutInput);
        }
        return $layout;
    }
}