<?php
declare(strict_types=1);
namespace Core\Exceptions;

class ViewException extends FrameworkException {
    public function __construct(
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}