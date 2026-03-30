<?php
declare(strict_types=1);

namespace Console;

/**
 * Wrapper class for InputInterface functions.
 */
trait ConsoleIO {
    /**
     * Wrapper for InputInterface::getArgument function.
     *
     * @param mixed $argument The argument
     * @return mixed The value for the argument
     */
    public function getArgument(mixed $argument): mixed {
        return $this->input->getOption($argument);
    }
}