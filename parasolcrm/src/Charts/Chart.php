<?php

namespace ParasolCRM\Charts;

use Illuminate\Database\Eloquent\Model;
use ParasolCRM\Charts\Interfaces\ChartInterface;
use ParasolCRM\Makeable;

abstract class Chart implements ChartInterface
{
    use Makeable;
    use ChartDateInterval;

    /** @var Model */
    public $model;

    /** @var string */
    public string $name = 'chart';

    /** @var string */
    public string $groupColumn = '';

    /** @var array */
    public array $columnsData = [];

    /** @var string */
    public string $pivot = '';

    /** @var int */
    protected int $borderWidth = 0;

    /** @var string */
    protected string $borderColor = '';

    /**
     * Chart constructor.
     *
     * @param  array  $columnsData
     * @param  string|null  $model
     */
    public function __construct(array $columnsData, string $model = null)
    {
        $this->columnsData($columnsData);
        $this->model = $model;
    }

    /**
     * Set model columns for use chart and action sum, agv
     *
     * @param  array  $columnsData
     *
     * @return void
     */
    protected function columnsData(array $columnsData): void
    {
        $this->columnsData = $columnsData;
    }

    /**
     * @param  string  $name
     *
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param  string  $column
     *
     * @return $this
     */
    public function groupColumn(string $column): self
    {
        $this->groupColumn = $column;
        return $this;
    }

    /**
     * @param  int  $borderWidth
     *
     * @return $this
     */
    public function borderWidth(int $borderWidth): self
    {
        $this->borderWidth = $borderWidth;
        return $this;
    }

    /**
     * @param  string  $borderColor
     *
     * @return $this
     */
    public function borderColor(string $borderColor): self
    {
        $this->borderColor = $borderColor;
        return $this;
    }

    /**
     * @param  array  $chartData
     *
     * @return array
     */
    protected function prepareData(array $chartData): array
    {
        if (count($this->intervals)) {
            foreach ($chartData as $column => &$item) {
                $item = array_merge($this->intervals, $item);
            }
        }

        return $chartData;
    }

    /**
     * @param  array  $chartData
     *
     * @return array
     */
    abstract public function chart(array $chartData): array;
}
