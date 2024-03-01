<?php

namespace ParasolCRM\Charts;

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
    protected string $minInterval = 'day';

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
     * @param  string  $format
     *
     * @return $this
     */
    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param  string  $minInterval
     *
     * @return $this
     */
    public function minInterval(string $minInterval): self
    {
        $this->minInterval = strtolower($minInterval);
        return $this;
    }

    /**
     * Set chart intervals and Line Chart labels
     *
     * @return string
     */
    public function setInterval(): string
    {
        $diffDays = $this->startDate && $this->endDate
            ? $this->startDate->diff($this->endDate)->days : 0;

        $diffDays++;

        foreach ($this->intervalOptions as $key => $value) {
            if ($diffDays >= $value || $this->minInterval === $key) {
                if ($key === 'week' || $key === 'custom_week') {
                    $this->getWeekInterval($key);
                    return 'WEEK';
                }
                if ($key === 'day') {
                    $this->getDayInterval();
                    return 'DAY';
                }
                if ($key === 'hour') {
                    $this->getHourInterval();
                    return 'HOUR';
                }
            }
        }
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
     * @param  Carbon  $start
     * @param  false  $first
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

    /**
     * @param  Carbon  $start
     * @param  false  $first
     *
     * @return string
     */
    protected function getDayLabel(Carbon $start, $first = false): string
    {
        $newMonth = $start->format('m') - $start->clone()->subDay()->format('m');
        $newYear = $start->format('Y') - $start->clone()->subDay()->format('Y');

        $format = ($first || $newMonth) ? 'd M' : 'd';
        $format = ($newYear) ? 'd M Y' : $format;

        return $start->format($format);
    }

    /**
     * @param  string  $key
     *
     * @return void
     */
    protected function getWeekInterval(string $key): void
    {
        $dates = [];
        $labels = [];
        $this->format = 'Y-m-d';

        $endWeek
            = $this->startDate->isSunday() ? $this->startDate->clone()->addWeek() : $this->startDate->clone()->endOfWeek(
            );

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
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @param  false  $first
     *
     * @return array
     */
    protected function getWeekLabel(Carbon $start, Carbon $end, $first = false): array
    {
        $newYear = $end->format('Y') - $start->format('Y');

        $endFormat = (!$first && $newYear) ? 'M d Y' : 'M d';
        $startFormat = (($first || $newYear) && ($endFormat !== 'M d Y')) ? 'M d Y' : 'M d';

        return [
            'date' => $start->format($startFormat).' - '.$end->format($endFormat),
            'newYear' => $newYear,
        ];
    }

    /**
     * @param  array  $labels
     * @param  string  $key
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
}
