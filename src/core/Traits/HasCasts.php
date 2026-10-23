<?php
declare(strict_types=1);
namespace Core\Traits;

use Carbon\CarbonInterface;
use Core\Lib\Utilities\DateFactory;
use InvalidArgumentException;

/**
 * Read-time date casting and JSON serialization for models.
 *
 * Model properties keep the raw values PDO loaded from the database
 * (UTC strings such as "2026-03-15 14:30:00").  Nothing about loading or
 * saving changes.  Casting happens only when you ask for it:
 *
 * $user->date('created_at');        // CarbonInterface in the app timezone
 * $user->toArray();                 // casts applied, dates serialized
 * json_encode($user);               // same as toArray()
 *
 * Declare casts with a property or a method (the method wins on conflict):
 *
 * protected array $casts = [
 *     'created_at' => 'datetime',
 *     'birthday'   => 'date',
 *     'starts_at'  => 'datetime:Y-m-d H:i',
 * ];
 *
 * protected function casts(): array {
 *     return ['published_at' => 'immutable_datetime'];
 * }
 *
 * Supported types:
 * - datetime, immutable_datetime: a moment in time stored in UTC.
 *   Serialized as ISO 8601 UTC, e.g. 2026-03-15T14:30:00Z.
 * - date, immutable_date: a calendar date with no time or timezone.
 *   Serialized as Y-m-d, e.g. 2026-03-15.
 * Append :format to any type to choose the serialized format instead.
 * Custom formats are rendered in the application timezone.
 */
trait HasCasts {
    /** Values treated as "no date". */
    private static array $emptyDates = ['', '0000-00-00', '0000-00-00 00:00:00'];

    /** Cast types this trait understands. */
    private static array $dateCastTypes = ['datetime', 'immutable_datetime', 'date', 'immutable_date'];

    /**
     * Returns the casts for this model.  Override to declare casts with a
     * method instead of the $casts property.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [];
    }

    /**
     * Returns all casts from the $casts property and the casts() method.
     *
     * @return array<string, string>
     */
    public function getCasts(): array {
        $declared = property_exists($this, 'casts') && is_array($this->casts) ? $this->casts : [];
        return array_merge($declared, $this->casts());
    }

    /**
     * Returns an attribute as a Carbon instance in the application timezone.
     *
     * Works for any attribute holding a date, whether or not it is declared
     * in $casts.  Undeclared attributes are treated as datetime.
     *
     * @param string $attribute The property name, e.g. 'created_at'.
     * @return CarbonInterface|null Null when the attribute is unset or empty.
     * @throws InvalidArgumentException If the attribute is cast to a non-date type.
     */
    public function date(string $attribute): ?CarbonInterface {
        [$type] = $this->parseCast($this->getCasts()[$attribute] ?? 'datetime');

        if(!isset($this->{$attribute})) {
            return null;
        }

        return $this->asDateTime($this->{$attribute}, $type);
    }

    /**
     * Converts a raw value into a Carbon instance according to a cast type.
     *
     * Datetime strings without a timezone are read as UTC.  Plain Y-m-d
     * strings are calendar dates and are never shifted between timezones.
     *
     * @param mixed $value Raw value: string, Unix timestamp, or DateTimeInterface.
     * @param string $type One of the supported cast types.
     * @return CarbonInterface|null
     */
    protected function asDateTime(mixed $value, string $type = 'datetime'): ?CarbonInterface {
        if($value === null || (is_string($value) && in_array(trim($value), self::$emptyDates, true))) {
            return null;
        }

        $isDateOnly = str_ends_with($type, 'date');

        if($isDateOnly && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $date = DateFactory::parse($value, DateFactory::timezone())->startOfDay();
        } else {
            $date = DateFactory::parse($value);
            if($isDateOnly) {
                $date = $date->startOfDay();
            }
        }

        return str_starts_with($type, 'immutable_') ? $date->toImmutable() : $date;
    }

    /**
     * Formats a datetime for JSON.  Override to change the wire format for
     * every datetime cast on a model that has no explicit :format.
     *
     * The default, ISO 8601 in UTC with a Z suffix, is parsed the same way
     * by every browser and by Chappy's JavaScript datetime utilities.
     *
     * @param CarbonInterface $date
     * @return string
     */
    protected function serializeDate(CarbonInterface $date): string {
        return $date->toImmutable()->utc()->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Returns the model's public properties with casts applied.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array {
        $attributes = $this->publicAttributes();

        foreach($this->getCasts() as $attribute => $cast) {
            [$type, $format] = $this->parseCast($cast);

            if(!array_key_exists($attribute, $attributes)) {
                continue;
            }

            $date = $this->asDateTime($attributes[$attribute], $type);

            $attributes[$attribute] = match(true) {
                $date === null => null,
                $format !== null => $date->format($format),
                str_ends_with($type, 'date') => $date->format('Y-m-d'),
                default => $this->serializeDate($date),
            };
        }

        return $attributes;
    }

    /**
     * Used by json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    /**
     * Splits "type:format" and validates the type.
     *
     * @param string $cast
     * @return array{0: string, 1: string|null}
     * @throws InvalidArgumentException For unsupported types.
     */
    private function parseCast(string $cast): array {
        [$type, $format] = array_pad(explode(':', $cast, 2), 2, null);

        if(!in_array($type, self::$dateCastTypes, true)) {
            throw new InvalidArgumentException(
                "Unsupported cast type \"{$type}\" on " . static::class .
                '. Supported: ' . implode(', ', self::$dateCastTypes)
            );
        }

        return [$type, $format === '' ? null : $format];
    }

    /**
     * Returns only public properties, exactly what json_encode() exposed
     * before this trait existed.  Called from outside class scope so
     * protected and private properties are excluded.
     *
     * @return array<string, mixed>
     */
    private function publicAttributes(): array {
        return \Closure::bind(static fn(object $o): array => get_object_vars($o), null, null)($this);
    }
}