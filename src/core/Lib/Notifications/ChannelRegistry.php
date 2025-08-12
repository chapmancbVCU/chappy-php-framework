<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use Core\Lib\Notifications\Contracts\Channel;
use RuntimeException;

final class ChannelRegistry {
    /**
     * @var array<string, class-string<Channel>>
     */
    private static array $map = [];

    public static function register(string $name, string $channelClass): void {
        self::$map[$name] = $channelClass;
    }

    public static function resolve(string $name): Channel {
        if(!isset(self::$map[$name])) {
            throw new RuntimeException("Unsupported notification channel: {$name}");
        }
        return new self::$map[$name]();
    }
}