<?php
declare(strict_types=1);
namespace Core\Lib\Utilities;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Single point of creation for all date objects in the framework.
 *
 * Every date Chappy creates should come from this class so that the
 * application timezone, locale, and choice of mutable or immutable Carbon
 * are applied consistently.  Because "now" always comes from Carbon,
 * Carbon::setTestNow() freezes time for everything built on this factory.
 */
class DateFactory {
    /**
     * The Carbon class used to build new instances.
     *
     * @var class-string<CarbonInterface>
     */
    protected static string $class = Carbon::class;

    /**
     * Sets the Carbon class used for all new instances.
     *
     * Call once during bootstrap, for example:
     * DateFactory::use(CarbonImmutable::class);
     *
     * @param string $class Carbon::class, CarbonImmutable::class, or a subclass.
     * @return void
     * @throws InvalidArgumentException If the class does not implement CarbonInterface.
     */
    public static function use(string $class): void {
        if(!is_a($class, CarbonInterface::class, true)) {
            throw new InvalidArgumentException("{$class} must implement " . CarbonInterface::class);
        }
        static::$class = $class;
    }

    /**
     * Sets the Carbon class from the DATE_CLASS environment value.
     * Accepts "immutable" or "mutable".  Defaults to mutable.
     *
     * @return void
     */
    public static function boot(): void {
        $setting = strtolower((string)Env::get('DATE_CLASS', 'mutable'));
        static::use($setting === 'immutable' ? CarbonImmutable::class : Carbon::class);
    }

    /**
     * Returns the Carbon class currently in use.
     *
     * @return string
     */
    public static function getClass(): string {
        return static::$class;
    }

    /**
     * Returns the current time.
     *
     * @param string|null $timezone Falls back to TIME_ZONE when null.
     * @return CarbonInterface
     */
    public static function now(?string $timezone = null): CarbonInterface {
        return static::configure(static::$class::now($timezone ?? static::timezone()));
    }

    /**
     * Returns midnight of the current day.
     *
     * @param string|null $timezone Falls back to TIME_ZONE when null.
     * @return CarbonInterface
     */
    public static function today(?string $timezone = null): CarbonInterface {
        return static::configure(static::$class::today($timezone ?? static::timezone()));
    }

    /**
     * Normalizes any supported input into a Carbon instance in the
     * application timezone.
     *
     * Accepts:
     * - DateTimeInterface instances
     * - Integer Unix timestamps (always treated as UTC)
     * - Date strings, interpreted in $fromTimezone
     *
     * @param mixed $value The value to convert.
     * @param string $fromTimezone Timezone the input string is stored in.
     * @return CarbonInterface
     */
    public static function parse(mixed $value, string $fromTimezone = 'UTC'): CarbonInterface {
        $date = match(true) {
            $value instanceof \DateTimeInterface => static::$class::instance($value),
            is_int($value) => static::$class::createFromTimestamp($value, 'UTC'),
            default => static::$class::parse((string)$value, $fromTimezone),
        };

        return static::configure($date->setTimezone(static::timezone()));
    }

    /**
     * Applies framework-wide settings to an instance.
     *
     * @param CarbonInterface $date
     * @return CarbonInterface
     */
    protected static function configure(CarbonInterface $date): CarbonInterface {
        return $date->locale((string)Env::get('LOCALE', 'en'));
    }

    /**
     * Returns the application timezone.
     *
     * @return string
     */
    protected static function timezone(): string {
        return (string)Env::get('TIME_ZONE', 'UTC');
    }
}