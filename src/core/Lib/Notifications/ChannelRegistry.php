<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use RuntimeException;
use Core\Lib\Utilities\Str;
use Core\Lib\Notifications\Contracts\Channel;
use Core\Lib\Notifications\Exceptions\UnregisteredChannelException;

/**
 * Registry for notification channel drivers.
 *
 * This static registry maps short channel names (e.g., "database", "mail") to
 * their concrete {@see Channel} implementation classes. Providers register
 * channels during application boot, and the notifier resolves them at runtime
 * before delivery.
 *
 * Notes:
 * - Registering the same name more than once will overwrite the previous mapping.
 * - {@see resolve()} instantiates the channel using a no-argument constructor.
 *   If your channel requires dependencies, adapt this class to use your container.
 *
 * Typical flow:
 * 1) A service/provider calls {@see ChannelRegistry::register()} during boot.
 * 2) When sending, {@see ChannelRegistry::resolve()} returns a Channel instance.
 *
 * @see \Core\Lib\Notifications\Contracts\Channel
 * @see \Core\Lib\Notifications\Notifiable::notify()
 * @see \Core\Lib\Notifications\NotificationManager::boot()
 */
final class ChannelRegistry {
    /**
     * Map of channel name => channel class.
     * @var array<string, class-string<Channel>>
     */
    private static array $map = [];

    /**
     * Return array of channel classes.
     *
     * @return array<string, class-string<Channel>> The array of channel 
     * classes.
     */
    public static function all(): array {
        return self::$map;
    }

    /**
     * Checks if channel name exists.
     *
     * @param string $name The name of the channel to check.
     * @return bool True if channel name exist.  Otherwise, we return false.
     */
    public static function has(string $name): bool {
        return isset(self::$map[Str::lower($name)]);
    }

    /**
     * Register (or override) a channel implementation under a short name.
     *
     * Call this during application boot (e.g., in a service provider).
     *
     * @param non-empty-string $name Short identifier used in Notification::via(), e.g. "database".
     * @param class-string<Channel> $channelClass Fully-qualified class name implementing {@see Channel}.
     *
     * @return void
     */
    public static function register(string $name, string $channelClass): void {
        self::$map[Str::lower($name)] = $channelClass;
    }

    /**
     * Resolve a channel by name into a concrete {@see Channel} instance.
     *
     * The channel class is instantiated with a zero-argument constructor.
     * If the channel is not registered, a {@see RuntimeException} is thrown.
     *
     * @param non-empty-string $name The short channel name to resolve.
     *
     * @return Channel The instantiated channel driver.
     *
     * @throws RuntimeException If the channel name is not registered.
     */
    public static function resolve(string $name): Channel {
        $key = Str::lower($name);
        if(!isset(self::$map[$key])) {
            throw new UnregisteredChannelException(
                'registry', 
                "Unsupported notification channel {$name}");
        }
        return new self::$map[$key]();
    }
}