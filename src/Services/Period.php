<?php

namespace Undjike\PlanSubscriptionSystem\Services;

use Carbon\Carbon;
use Exception;

class Period
{
    /**
     * Starting date of the period.
     *
     * @var string|Carbon
     */
    protected $start;

    /**
     * Ending date of the period.
     *
     * @var string|Carbon
     */
    protected $end;

    /**
     * Interval.
     *
     * @var string
     */
    protected $interval;

    /**
     * Interval count.
     *
     * @var int
     */
    protected $period = 1;

    /**
     * Create a new Period instance.
     *
     * @param ?string|Carbon $start
     *
     * @param string $interval
     * @param int $count
     * @throws Exception
     */
    public function __construct($start = null, string $interval = 'month', int $count = 1)
    {
        $this->interval = $interval;

        if (empty($start)) $this->start = now();
        elseif (!$start instanceof Carbon) $this->start = new Carbon($start);
        else $this->start = $start;

        if ($count > 0) $this->period = $count;

        $start = clone $this->start;
        $method = 'add'.ucfirst($this->interval).'s';
        $this->end = $start->{$method}($this->period);
    }

    /**
     * Get start date.
     *
     * @return Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    /**
     * Get end date.
     *
     * @return Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    /**
     * Get period interval.
     *
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * Get period interval count.
     *
     * @return int
     */
    public function getIntervalCount(): int
    {
        return $this->period;
    }
}
