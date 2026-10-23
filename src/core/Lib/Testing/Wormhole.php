<?php
declare(strict_types=1);
namespace Core\Lib\Testing;

use Carbon\CarbonInterface;
use Closure;
use Core\Lib\Utilities\DateFactory;

/**
 * Fluent unit selector returned by InteractsWithTime::travel().
 *
 * $this->travel(5)->minutes();
 * $this->travel(-1)->day(function() { ... });
 *
 * Each method moves time relative to the current (possibly frozen) time.
 * Without a callback the new time stays in effect and the new instance is
 * returned.  With a callback, time is moved only while the callback runs
 * and the callback's return value is returned.
 */
class Wormhole {
    /**
     * @param int $value The amount of time to travel.
     * @param Closure(CarbonInterface, ?callable): mixed $travelTo Performs the actual travel.
     */
    public function __construct(
        private readonly int $value,
        private readonly Closure $travelTo
    ) {}

    public function seconds(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addSeconds($this->value), $callback);
    }

    public function minutes(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addMinutes($this->value), $callback);
    }

    public function hours(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addHours($this->value), $callback);
    }

    public function days(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addDays($this->value), $callback);
    }

    public function weeks(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addWeeks($this->value), $callback);
    }

    /**
     * Months never overflow: January 31 plus one month is February 28 (or 29),
     * not March 3.
     */
    public function months(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addMonthsNoOverflow($this->value), $callback);
    }

    /**
     * Years never overflow: February 29 plus one year is February 28.
     */
    public function years(?callable $callback = null): mixed {
        return $this->go(DateFactory::now()->addYearsNoOverflow($this->value), $callback);
    }

    /** Singular aliases so travel(1)->day() reads naturally. */
    public function second(?callable $callback = null): mixed { return $this->seconds($callback); }
    public function minute(?callable $callback = null): mixed { return $this->minutes($callback); }
    public function hour(?callable $callback = null): mixed { return $this->hours($callback); }
    public function day(?callable $callback = null): mixed { return $this->days($callback); }
    public function week(?callable $callback = null): mixed { return $this->weeks($callback); }
    public function month(?callable $callback = null): mixed { return $this->months($callback); }
    public function year(?callable $callback = null): mixed { return $this->years($callback); }

    /**
     * Travels to the computed moment.
     *
     * @param CarbonInterface $date
     * @param callable|null $callback
     * @return mixed The new instance without a callback, otherwise the callback's return value.
     */
    private function go(CarbonInterface $date, ?callable $callback): mixed {
        $result = ($this->travelTo)($date, $callback);
        return $callback === null ? $date : $result;
    }
}