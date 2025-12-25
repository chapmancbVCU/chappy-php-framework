<?php
declare(strict_types=1);
namespace Core\Lib\Events\Contracts;

/**
 * Marker interface indicating that an event listener should be queued.
 *
 * Implement this interface on an event listener to signal the dispatcher
 * that the listener must be executed asynchronously via the queue system
 * rather than running immediately during event dispatch.
 *
 * Example:
 * ```php
 * class SendWelcomeEmail implements ShouldQueue
 * {
 *     // Listener logic...
 * }
 * ```
 */
interface ShouldQueue {}