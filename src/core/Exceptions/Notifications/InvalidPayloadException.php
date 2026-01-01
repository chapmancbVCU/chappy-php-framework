<?php
declare(strict_types=1);

namespace Core\Exceptions\Notifications;

/**
 * Handles cases when there are invalid payloads.  Extends the 
 * NotificationException class.
 */
final class InvalidPayloadException extends NotificationException {}