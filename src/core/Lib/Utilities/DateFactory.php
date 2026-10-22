<?php
declare(strict_types=1);
namespace Core\Lib\Utilities;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class DateFactory {

    protected static string $class = Carbon::class;

    public static function use(string $class): void {
        static::$class = $class;
    }

    public static function now(?string $tz = null): CarbonInterface {
        return static::configure(static::$class::now($tz ?? static::timezone()));
    }

    public static function today(?string $tz = null): CarbonInterface {
        return static::configure(static::$class::today($tz ?? static::timezone()));
    }

    /** Normalize any input: string, timestamp, DateTimeInterface. */
    public static function parse(mixed $value, string $fromTz = 'UTC'): CarbonInterface {
        $date = match (true) {
            $value instanceof \DateTimeInterface => static::$class::instance($value),
            is_int($value)                       => static::$class::createFromTimestamp($value, 'UTC'),
            default                              => static::$class::parse((string)$value, $fromTz),
        };
        return static::configure($date->setTimezone(static::timezone()));
    }

    protected static function configure(CarbonInterface $date): CarbonInterface {
        return $date->locale(Env::get('LOCALE', 'en'));
    }

    protected static function timezone(): string {
        return Env::get('TIME_ZONE', 'UTC');
    }
    
}