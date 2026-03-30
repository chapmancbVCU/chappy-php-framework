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
     * @param mixed $argument The argument.
     * @return mixed The value for the argument.
     */
    public function getArgument(mixed $argument): mixed {
        return $this->input->getArgument($argument);
    }

    /**
     * Wrapper for InputInterface::getOption function.
     *
     * @param mixed $option The option.
     * @return mixed The value for the option.
     */
    public function getOption(mixed $option): mixed {
        return $this->input->getOption($option);
    }
}