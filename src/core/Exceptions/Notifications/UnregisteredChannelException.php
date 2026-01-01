<?php
declare(strict_types=1);

namespace Core\Exceptions\Notifications;

/**
 * Handles exceptions related to unregistered channels.  Extends the 
 * ChannelException class.
 */
final class UnregisteredChannelException extends ChannelException {}