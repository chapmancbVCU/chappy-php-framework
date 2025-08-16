<?php
declare(strict_types=1);

namespace Core\Lib\Notifications;

/**
 * Enumerated type for channel names.
 */
enum Channel: string{
    case DATABASE = 'database';
    case LOG      = 'log';
    case MAIL     = 'mail';
}