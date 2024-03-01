<?php

namespace ParasolCRMV2\Charts;

use Carbon\Carbon;

trait ChartDateInterval
{
    /** @var $startDate Carbon */
    public Carbon $startDate;

    /** @var $endDate Carbon */
    public Carbon $endDate;

    /** @var array */
    protected array $intervals = [];

    /** @var string */
    protected string $interval = 'day';

    /** @var string */
    public string $format = 'Y-m-d';

    /** @var array */
    protected array $intervalOptions = [
        'custom_week' => 263,
        'week' => 64,
        'day' => 3,
        'hour' => 1,
    ];

    /** @var array */
    public array $labels = [];

    /** @var bool */
    protected bool $getLabel = false;

    /**
     * @param string $format
     *
     * @return $this
     */
    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param string $minInterval
     *
     * @return $this
     */
    public function setInterval(string $minInterval): self
    {
        $this->interval = strtolower($minInterval);
        return $this;
    }

    public function getIntervalName(): string
    {
        if ($this->interval) {
            return $this->interval;
        }

        $diffDays = $this->startDate->diff($this->endDate)->days + 1;

        foreach ($this->intervalOptions as $key => $value) {
            if ($diffDays >= $value || $this->interval === $key) {
                return $key;
            }
        }

        return $this->interval;
    }

    /**
     * Set chart intervals and Line Chart labels
     */
    public function fillIntervals(?string $interval = null): void
    {
        $interval ??= $this->getIntervalName();

        match ($interval) {
            'month' => $this->getMonthInterval(),
            'week' => $this->getWeekInterval('week'),
            'custom_week' => $this->getWeekInterval('custom_week'),
            'day' => $this->getDayInterval(),
            'hour' => $this->getHourInterval(),
        };
    }

    /**
     * @return void
     */
    protected function getHourInterval(): void
    {
        $dates = [];
        $labels = [];
        $this->format = 'Y-m-d H';

        !$this->getLabel ?: array_push($labels, $this->getHourLabel($this->startDate, true));
        $dates += [$this->startDate->format($this->format) => 0];
        $endDate = $this->startDate->clone()->endOfHour();

        for (; ;) {
            if ($endDate >= $this->endDate) {
                break;
            }
            $startDate = $endDate->clone()->addHour()->startOfHour();
            $endDate = ($endDate->clone()->addHour()->endOfHour()->greaterThan($this->endDate))
                ? $this->endDate
                : $endDate->clone()->addHour()->endOfHour();

            $dates += [$startDate->format($this->format) => 0];
            !$this->getLabel ?: array_push($labels, $this->getHourLabel($startDate));
        }

        $this->intervals = $dates;
        !$this->getLabel ?: $this->labels = $labels;
    }

    /**
     * @param Carbon $start
     * @param false $first
     *
     * @return string
     */
    protected function getHourLabel(Carbon $start, $first = false): string
    {
        $newDay = $start->format('d') - $start->clone()->subHour()->format('d');
        $newMonth = $start->format('m') - $start->clone()->subHour()->format('m');

        $format = ($first || $newMonth || $newDay) ? 'H:i d M' : 'H:i';

        return $start->format($format);
    }

    /**
     * @return void
     */
    protected function getDayInterval(): void
    {
        $dates = [];
        $labels = [];
        $this->format = 'Y-m-d';

        !$this->getLabel ?: array_push($labels, $this->getDayLabel($this->startDate, true));
        $dates += [$this->startDate->format($this->format) => 0];
        $endDate = $this->startDate->clone()->endOfDay();

        for (; ;) {
            if ($endDate >= $this->endDate) {
                break;
            }
            $startDate = $endDate->clone()->addDay()->startOfDay();
            $endDate = ($endDate->clone()->addDay()->endOfDay()->greaterThan($this->endDate))
                ? $this->endDate
                : $endDate->clone()->addDay()->endOfDay();

            $dates += [$startDate->format($this->format) => 0];
            !$this->getLabel ?: array_push($labels, $this->getDayLabel($startDate));
        }

        $this->intervals = $dates;
        !$this->getLabel ?: $this->labels = $labels;
    }

    protected function getDayLabel(Carbon $start, bool $first = false)
    {
        $newMonth = $start->format('m') - $start->clone()->subDay()->format('m');
        $newYear = $start->format('Y') - $start->clone()->subDay()->format('Y');

        $result[] = $start->format('d');
        if ($first || $newMonth) {
            $result[] = $start->format('M');
        }
        if ($newYear) {
            $result[] = $start->format('Y');
        }

        return $result;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    protected function getWeekInterval(string $key): void
    {
        $dates = [];
        $labels = [];
        $this->format = 'Y-W';

        $endWeek = $this->startDate->clone()->endOfWeek();

        !$this->getLabel ?: array_push($labels, $this->getWeekLabel($this->startDate, $endWeek, true));
        $dates += [$this->startDate->format($this->format) => 0];

        for (; ;) {
            if ($endWeek >= $this->endDate) {
                break;
            }
            $startDate = $endWeek->clone()->addDay()->startOfDay();
            $endWeek = ($endWeek->clone()->addDay()->endOfWeek()->endOfDay()->greaterThan($this->endDate))
                ? $this->endDate
                : $endWeek->clone()->addDay()->endOfWeek()->endOfDay();

            $dates += [$startDate->format($this->format) => 0];
            !$this->getLabel ?: array_push($labels, $this->getWeekLabel($startDate, $endWeek));

            unset($startDate);
        }
        !$this->getLabel ?: $this->labels = $this->changeWeekLabel($labels, $key);

        $this->intervals = $dates;
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @param false $first
     *
     * @return array
     */
    protected function getWeekLabel(Carbon $start, Carbon $end, $first = false): array
    {
        $newYear = $end->format('Y') - $start->format('Y');

        $endFormat = (!$first && $newYear) ? 'd M Y' : 'd M';
        $startFormat = (($first || $newYear) && ($endFormat !== 'd M Y')) ? 'd M Y' : 'd M';

        return [
            'date' => $start->format($startFormat),
            'newYear' => $newYear,
        ];
    }

    /**
     * @param array $labels
     * @param string $key
     *
     * @return array
     */
    protected function changeWeekLabel(array $labels, string $key): array
    {
        if ($key === 'week') {
            return array_column($labels, 'date');
        }

        $firstKey = array_key_first($labels);
        $lastKey = array_key_last($labels);

        /* If the difference in days is more than 263 days,
        then the signatures of the schedule in a week except those affixed over the years */
        foreach ($labels as $key => $label) {
            if (($firstKey !== $key) && ($lastKey !== $key) && ($key & 1) && ($label['newYear'] !== 1)) {
                $labels[$key] = ' ';
                continue;
            }
            $labels[$key] = $label['date'];
        }
        return $labels;
    }

    /**
     * @return void
     */
    protected function getMonthInterval(): void
    {
        $dates = [];
        $labels = [];
        $this->format = 'Y-m';

        !$this->getLabel ?: array_push($labels, $this->getMonthLabel($this->startDate));
        $dates += [$this->startDate->format($this->format) => 0];
        $endDate = $this->startDate->clone()->endOfMonth()->endOfDay();

        for (; ;) {
            if ($endDate >= $this->endDate) {
                break;
            }

            $startDate = $endDate->clone()->addMonthsNoOverflow()->endOfMonth();
            $endDate = ($endDate->clone()->addMonthsNoOverflow()->endOfMonth()->greaterThan($this->endDate))
                ? $this->endDate
                : $endDate->clone()->addMonthsNoOverflow()->endOfMonth();

            $dates += [$startDate->format($this->format) => 0];
            !$this->getLabel ?: array_push($labels, $this->getMonthLabel($startDate));
        }

        $this->intervals = $dates;
        !$this->getLabel ?: $this->labels = $labels;
    }

    /**
     * @param Carbon $start
     * @param false $first
     *
     * @return string
     */
    protected function getMonthLabel(Carbon $start): string
    {
        return $start->format('M Y');
    }
}
