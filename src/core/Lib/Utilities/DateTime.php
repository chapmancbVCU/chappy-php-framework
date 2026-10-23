<?php
declare(strict_types=1);
namespace Core\Lib\Utilities;

use Carbon\Carbon;

/**
 * Supports ability to manipulate how time is displayed.  Most functions are
 * wrappers for those found in the Carbon class.
 *
 * All date objects are created through DateFactory so timezone, locale,
 * and Carbon::setTestNow() behave consistently.
 */
class DateTime {
    /** 12 hour format. */
    public const FORMAT_12_HOUR = 'Y-m-d h:i:s A';
    /** 24 hour format. */
    public const FORMAT_24_HOUR = 'Y-m-d H:i:s';
    /** Human readable format. */
    public const FORMAT_HUMAN_READABLE = 'l, F j, Y g:i A';
    /** Date only. */
    public const FORMAT_DATE_ONLY = 'Y-m-d';
    /** Time only in 12 hour format */
    public const FORMAT_TIME_ONLY_12H = 'h:i A';
    /** Time only in 24 hour format */
    public const FORMAT_TIME_ONLY_24H = 'H:i';
    /** Friendly date format. */
    public const FORMAT_FRIENDLY_DATE = 'F j, Y';
    /** Day date format. */
    public const FORMAT_DAY_DATE = 'l, M j';
    /** ISO 8601 format. */
    public const FORMAT_ISO_8601 = 'c';
    /** RFC 2822 format. */
    public const FORMAT_RFC_2822 = 'r';
    /** SQL DateTime format. */
    public const FORMAT_SQL_DATETIME = 'Y-m-d H:i:s';

    /**
     * Returns string that describes time.  The results can be set using
     * constants, locale, and timezone.
     *
     * @param string|\DateTimeInterface $time String in format Y-m-d H:i:s A using UTC,
     *                                    or a date object (its own timezone is used).
     * @param string $format Set format with a default of FORMAT_12_HOUR.
     * @param string $locale Set locale with 'en' as the default value.
     * @param string $timezone Timezone a string $time is stored in, with 'UTC' as default value.
     *                         Ignored for date objects.
     * @return string The formatted time in the application timezone.
     */
    public static function formatTime(string|\DateTimeInterface $time, string $format = self::FORMAT_12_HOUR, string $locale = 'en', string $timezone = 'UTC'): string {
        return DateFactory::parse($time, $timezone)
            ->locale($locale)
            ->translatedFormat($format);
    }

    /**
     * Get the current time as a formatted string using Carbon.
     *
     * This function supports optional user-specific overrides for timezone,
     * format, and locale. If not provided, it defaults to the application's
     * configured environment values.
     *
     * @param string|null $timezone The timezone to use (e.g., 'Europe/Berlin').
     *                              Falls back to TIME_ZONE if null.
     * @param string|null $format   The format string (e.g., 'H:i' or 'g:i A').
     *                              Falls back to DateTime::FORMAT_12_HOUR if null.
     * @param string|null $locale   The locale code (e.g., 'en', 'fr').
     *                              Falls back to LOCALE if null.
     *
     * @return string The current time formatted based on the provided or default settings.
     */
    public static function now(?string $timezone = null, ?string $format = null, ?string $locale = null): string {
        return self::present(DateFactory::now($timezone), $format, $locale);
    }

    /**
     * Adds second passed as parameter to current time.
     *
     * @param int $seconds The number of seconds to add.
     * @return string The time plus seconds passed as parameter in UTC.
     */
    public static function nowPlusSeconds(int $seconds): string {
        return DateFactory::now('UTC')
            ->addSeconds($seconds)
            ->format(self::FORMAT_SQL_DATETIME);
    }

    /**
     * Accepts UTC time in format Y-m-d H:i:s and returns a string describing
     * how much time has elapsed.
     *
     * This function supports a short form with the following example:
     * DateTime::timeAgo($user->updated_at, 'en', 'UTC', true);
     *
     * This will show something like 21m.
     *
     * @param string|\DateTimeInterface $time String in format Y-m-d H:i:s using UTC,
     *                                    or a date object (its own timezone is used).
     * @param string $locale Set locale with 'en' as the default value.
     * @param string $timezone Timezone a string $time is stored in, with 'UTC' as default value.
     *                         Ignored for date objects.
     * @param bool $short Set to true to show short form time.
     * @return string The time represented using language describing time since last change.
     */
    public static function timeAgo(string|\DateTimeInterface $time, string $locale = 'en', string $timezone = 'UTC', bool $short = false): string {
        $carbon = DateFactory::parse($time, $timezone)->locale($locale);

        return $short
            ? $carbon->diffForHumans(null, false, true) // Short format
            : $carbon->diffForHumans(); // Default long format
    }

    /**
     * Shows the difference between two times.  An example is shown below:
     * DateTime::timeDifference('2025-03-09 08:00:00', '2025-03-09 15:30:45');
     * Output: "7 hours before"
     *
     * @param string|\DateTimeInterface $startTime String in format Y-m-d H:i:s using UTC, or a date object.
     * @param string|\DateTimeInterface $endTime String in format Y-m-d H:i:s using UTC, or a date object.
     * @param string $timezone Timezone both times are stored in, with 'UTC' as default value.
     * @return string Show exact difference between two times.
     */
    public static function timeDifference(string|\DateTimeInterface $startTime, string|\DateTimeInterface $endTime, string $timezone = 'UTC'): string {
        $start = DateFactory::parse($startTime, $timezone);
        $end = DateFactory::parse($endTime, $timezone);
        return $start->diffForHumans($end);
    }

    /**
     * Generates a timestamp.
     *
     * @return string A timestamp in the format Y-m-d H:i:s UTC time.
     */
    public static function timeStamps(): string {
        return DateFactory::now('UTC')->format(self::FORMAT_SQL_DATETIME);
    }

    /**
     * Get the current day as a formatted string using Carbon.
     *
     * This function supports optional user-specific overrides for timezone,
     * format, and locale. If not provided, it defaults to the application's
     * configured environment values.
     *
     * @param string|null $timezone The timezone to use (e.g., 'Europe/Berlin').
     *                              Falls back to TIME_ZONE if null.
     * @param string|null $format   The format string (e.g., 'H:i' or 'g:i A').
     *                              Falls back to DateTime::FORMAT_12_HOUR if null.
     * @param string|null $locale   The locale code (e.g., 'en', 'fr').
     *                              Falls back to LOCALE if null.
     *
     * @return string The current day formatted based on the provided or default settings.
     */
    public static function today(?string $timezone = null, ?string $format = null, ?string $locale = null): string {
        return self::present(DateFactory::today($timezone), $format, $locale);
    }

    /**
     * Converts to ISO 8601 format.
     *
     * @param string|\DateTimeInterface $time String in format Y-m-d H:i:s using UTC,
     *                                    or a date object (its own timezone is used).
     * @param string $timezone Timezone a string $time is stored in, with 'UTC' as default value.
     *                         Ignored for date objects.
     * @return string The time in ISO 8601 format.  Example output: 2025-03-09T15:30:45-05:00
     */
    public static function toISO8601(string|\DateTimeInterface $time, string $timezone = 'UTC'): string {
        $carbon = $time instanceof \DateTimeInterface ? Carbon::instance($time) : Carbon::parse($time, $timezone);
        return $carbon->toIso8601String();
    }

    /**
     * Formats an instance that is already in the timezone the caller wants.
     *
     * @param \Carbon\CarbonInterface $date
     * @param string|null $format Falls back to FORMAT_12_HOUR.
     * @param string|null $locale Falls back to LOCALE.
     * @return string
     */
    protected static function present(\Carbon\CarbonInterface $date, ?string $format, ?string $locale): string {
        return $date
            ->locale($locale ?? (string)Env::get('LOCALE', 'en'))
            ->translatedFormat($format ?? self::FORMAT_12_HOUR);
    }
}