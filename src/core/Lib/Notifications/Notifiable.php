<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use Core\Lib\Utilities\Str;
use Core\Models\Notifications;
use Core\Lib\Notifications\ChannelRegistry;

/**
 * Adds notification capabilities to a model.
 *
 * This trait allows any model to send notifications through one or more
 * channels (e.g., database, log, mail) using your Notification system.
 *
 * Requirements/Assumptions:
 * - The consuming model exposes an integer `$id` property (used by `notifications()`).
 * - Channel drivers are registered in {@see \Core\Lib\Notifications\ChannelRegistry}.
 * - Each Notification may optionally implement per-channel methods such as
 *   `toDatabase(object $notifiable): array`, `toLog(object $notifiable): string`,
 *   `toMail(object $notifiable): array`, etc. If a `to{Channel}` method is
 *   missing, `notify()` passes `null` as the `message` payload for that channel.
 */
trait Notifiable {
    /**
     * Send a notification to this notifiable through one or more channels.
     *
     * Resolves channels from the provided `$channels` argument or, if omitted,
     * from `$notification->via($this)`. For each channel, this method looks for a
     * corresponding payload method on the notification named `to{Channel}` (e.g.,
     * `toDatabase`, `toLog`, `toMail`). The result (or `null` if the method
     * does not exist) is placed under the `message` key, and the `$payload` array
     * is included under `meta`. The combined payload is then sent to the channel
     * driver resolved by {@see \Core\Lib\Notifications\ChannelRegistry::resolve()}.
     *
     * @param \Core\Lib\Notifications\Notification $notification
     *        The notification instance to send.
     * @param list<\Core\Lib\Notifications\Channel|string>|null $channels
     *        Optional explicit list of channels to use. Each item may be either a
     *        `Channel` enum case or a lowercase string channel name (e.g. 'database').
     *        When `null`, channels are taken from `$notification->via($this)`.
     * @param array<string,mixed> $payload
     *        Optional supplemental metadata to include under the `meta` key for every
     *        channel (e.g., correlation IDs, debug flags). Defaults to an empty array.
     *
     * @return void
     *
     * @throws \RuntimeException If a requested channel has no registered driver.
     * @throws \Throwable        Any exception thrown by a channel driver will bubble up.
     */
    public function notify(
        Notification $notification,
        ?array $channels = null,
        array $payload = []
    ): void {
        $resolved = $channels ?? $notification->via($this);

        foreach ($resolved as $channel) {
            $name = $channel instanceof \Core\Lib\Notifications\Channel ? $channel->value : (string)$channel;
            $toMethod = 'to' . ucfirst($name);

            $messagePayload = method_exists($notification, $toMethod)
                ? $notification->{$toMethod}($this)
                : null;

            // ✅ Keep array payloads top-level (mail/database), wrap only strings (log, etc.)
            if (is_array($messagePayload)) {
                $channelPayload = $messagePayload;                // preserve expected keys
                if (!empty($payload)) {
                    // attach overrides/metadata without colliding with channel keys
                    $channelPayload['_meta'] = ($channelPayload['_meta'] ?? []) + $payload;
                }
            } else {
                // string/int/bool/null → normalize to a message field
                $channelPayload = ['message' => $messagePayload] + ($payload ?: []);
            }

            $driver = ChannelRegistry::resolve($name);
            $driver->send($this, $notification, $channelPayload);
        }
    }

    /**
     * Retrieve this notifiable's unread notifications, newest first.
     *
     * Queries the notifications table for rows where `notifiable_id = $this->id`
     * and `read_at IS NULL`, ordered by `created_at DESC`.
     *
     * @return array An array of unread notification records (model instances) for this notifiable.
     *               Returns an empty array if none are found.
     */
    public function notifications(): array {
        $results = Notifications::find([
            'conditions' => 'notifiable_id = ? AND read_at IS NULL',
            'bind' => [$this->id],
            'order' => 'created_at DESC'
        ]);
        return is_array($results) ? $results : [];
    }
}