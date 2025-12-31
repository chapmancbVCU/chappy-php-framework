<?php
declare(strict_types=1);
namespace Core\Lib\Utilities;

use Carbon\Carbon;

/**
 * Supports ability to manipulate how time is displayed.  Most functions are 
 * wrappers for those found in the Carbon class.
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
     * @param string $time String in format Y-m-d H:i:s A using UTC.
     * @param string $format Set format with a default of FORMAT_12_HOUR.
     * @param string $locale Set locale with 'en' as the default value.
     * @param string $timezone Override default timezone with 'UTC' as default value.
     * @return string The formatted time.
     */
    public static function formatTime(string $time, string $format = self::FORMAT_12_HOUR, string $locale = 'en', string $timezone = 'UTC'): string {
        $carbon = Carbon::parse($time, $timezone)->setTimezone(Env::get('TIME_ZONE'));
    
        // Temporarily set the locale for this instance only
        return $carbon->locale($locale)->translatedFormat($format);
    }

    /**
     * Adds second passed as parameter to current time.
     *
     * @param int $seconds The number of seconds to add.
     * @return string The time plus seconds passed as parameter.
     */
    public static function nowPlusSeconds(int $seconds): string {
        $dt = new \DateTime("now", new \DateTimeZone("UTC"));
        $dt->modify("+{$seconds} seconds");
        return $dt->format('Y-m-d H:i:s');
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
     * @param string $time String in format Y-m-d H:i:s using UTC.
     * @param string $locale Set locale with 'en' as the default value.
     * @param string $timezone Override default timezone with 'UTC' as default value.
     * @param bool $short Set to true to show short form time.
     * @return string The time represented using language describing time since last change.
     */
    public static function timeAgo(string $time, string $locale = 'en', string $timezone = 'UTC', bool $short = false): string {
        $carbon = Carbon::parse($time, $timezone)
            ->setTimezone(Env::get('TIME_ZONE'))
            ->locale($locale); // Set locale per instance
    
        return $short 
            ? $carbon->diffForHumans(null, false, true) // Short format
            : $carbon->diffForHumans(); // Default long format
    }
    
    /**
     * Shows the difference between two times.  An example is shown below:
     * DateTimeHelper::timeDifference('2025-03-09 08:00:00', '2025-03-09 15:30:45');
     * Output: "7 hours before"
     *
     * @param string $startTime String in format Y-m-d H:i:s using UTC.
     * @param string $endTime String in format Y-m-d H:i:s using UTC.
     * @param string $timezone Override default timezone with 'UTC' as default value.
     * @return string Show exact difference between two times.
     */
    public static function timeDifference(string $startTime, string $endTime, string $timezone = 'UTC'): string {
        $start = Carbon::parse($startTime, $timezone);
        $end = Carbon::parse($endTime, $timezone);
        return $start->diffForHumans($end);
    }
    
    /**
     * Generates a timestamp.
     *
     * @return string A timestamp in the format Y-m-d H:i:s UTC time.
     */
    public static function timeStamps(): string {
        $dt = new \DateTime("now", new \DateTimeZone("UTC"));
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Converts to ISO 8610 format.
     *
     * @param string $time String in format Y-m-d H:i:s using UTC.
     * @param string $timezone Override default timezone with 'UTC' as default value.
     * @return string The time in ISO 8610 format.  Example output: 2025-03-09T15:30:45-05:00
     */
    public static function toISO8601(string $time, string $timezone = 'UTC'): string {
        return Carbon::parse($time, $timezone)->toIso8601String();
    }
}