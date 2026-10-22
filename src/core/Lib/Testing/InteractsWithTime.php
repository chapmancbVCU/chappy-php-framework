<?php
declare(strict_types=1);
namespace Core\Lib\Testing;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Core\Lib\Utilities\DateFactory;
use PHPUnit\Framework\Attributes\After;

/**
 * Time travel helpers for tests.
 *
 * Works in both unit tests (plain PHPUnit TestCase) and feature tests
 * (ApplicationTestCase).  Every helper sets Carbon's test "now", so it
 * affects anything that creates dates through DateFactory or Carbon.
 *
 * The real clock is restored automatically after each test.
 *
 * Examples:
 * $this->freezeTime();
 * $this->travelTo('2026-03-15 14:30:00');
 * $this->travel(5)->minutes();
 * $this->travel(-2)->days();
 * $this->travelBack();
 */
trait InteractsWithTime {
    /**
     * Freezes time at the current moment.
     *
     * Without a callback, time stays frozen until travelBack() or the end
     * of the test, and the frozen instance is returned.  With a callback,
     * time is frozen only while the callback runs, and the callback's
     * return value is returned.
     *
     * @param callable|null $callback Receives the frozen instance.
     * @return mixed
     */
    protected function freezeTime(?callable $callback = null): mixed {
        $now = DateFactory::now();
        $result = $this->travelTo($now, $callback === null ? null : fn() => $callback($now));

        return $callback === null ? $now : $result;
    }

    /**
     * Freezes time at the start of the current second, dropping microseconds.
     *
     * Useful when comparing against values stored in the database, which
     * usually have no microsecond precision.
     *
     * @param callable|null $callback Receives the frozen instance.
     * @return mixed
     */
    protected function freezeSecond(?callable $callback = null): mixed {
        $now = DateFactory::now()->startOfSecond();
        $result = $this->travelTo($now, $callback === null ? null : fn() => $callback($now));

        return $callback === null ? $now : $result;
    }

    /**
     * Moves time to a specific moment.
     *
     * Strings are interpreted as UTC, matching how Chappy stores dates.
     * Integers are treated as Unix timestamps.
     *
     * Without a callback, time stays at $date until travelBack() or the end
     * of the test.  With a callback, the clock returns to its previous state
     * after the callback runs, even if it throws.
     *
     * @param \DateTimeInterface|string|int $date The moment to travel to.
     * @param callable|null $callback Optional code to run at that moment.
     * @return mixed The callback's return value, or null without a callback.
     */
    protected function travelTo(\DateTimeInterface|string|int $date, ?callable $callback = null): mixed {
        $previous = Carbon::getTestNow();
        Carbon::setTestNow(DateFactory::parse($date));

        if($callback === null) {
            return null;
        }

        try {
            return $callback();
        } finally {
            Carbon::setTestNow($previous);
        }
    }

    /**
     * Moves time relative to the current (possibly already frozen) time.
     *
     * Use negative values to move backward:
     * $this->travel(-3)->hours();
     *
     * @param int $value The amount of time to travel.
     * @return Wormhole
     */
    protected function travel(int $value): Wormhole {
        return new Wormhole($value, fn(CarbonInterface $date, ?callable $callback) => $this->travelTo($date, $callback));
    }

    /**
     * Restores the real clock.
     *
     * @return CarbonInterface The current real time.
     */
    protected function travelBack(): CarbonInterface {
        Carbon::setTestNow();
        return DateFactory::now();
    }

    /**
     * Restores the real clock after every test so frozen time never leaks
     * into the next test.
     *
     * @return void
     */
    #[After]
    protected function restoreRealTime(): void {
        Carbon::setTestNow();
    }
}