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

    /**
     * Wrapper for InputInterface::hasOption function.
     *
     * @param string $name The name for the option.
     * @return bool True if exists, otherwise false.
     */
    public function hasOption(string $name): bool {
        return $this->input->hasOption($name);
    }
}