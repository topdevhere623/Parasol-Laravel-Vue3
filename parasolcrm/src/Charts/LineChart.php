<?php

namespace ParasolCRM\Charts;

use Illuminate\Support\Arr;

class LineChart extends Chart
{
    /** @var string */
    public string $name = 'lineChart';

    /** @var bool */
    protected bool $fill = false;

    /** @var array */
    public array $preset = [];

    /**
     * Chart constructor.
     *
     * @param  array  $columnsData
     * @param  string|null  $model
     */
    public function __construct(array $columnsData, string $model = null)
    {
        parent::__construct($columnsData, $model);
        $this->getLabel = true;
    }

    /**
     * @param  bool  $fill
     *
     * @return $this
     */
    public function fill(bool $fill): self
    {
        $this->fill = $fill;
        return $this;
    }

    /**
     * @param  array  $preset
     *
     * @return $this
     */
    public function preset(array $preset): self
    {
        $this->preset = $preset;
        return $this;
    }

    /**
     * @return array
     */
    protected function getFirstPreset(): array
    {
        $data = ['datasets' => []];
        $interval = count($this->intervals);

        foreach ($this->preset as $key => $preset) {
            if (!isset($preset['showOnLast']) || !$preset['showOnLast']) {
                $data['datasets'][] = $this->getPresetData($preset, $key, $interval);
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getLastPreset(): array
    {
        $data = ['datasets' => []];
        $interval = count($this->intervals);

        foreach ($this->preset as $key => $preset) {
            if (isset($preset['showOnLast']) && $preset['showOnLast']) {
                $data['datasets'][] = $this->getPresetData($preset, $key, $interval);
            }
        }
        return $data;
    }

    /**
     * @param  array  $preset
     * @param  string  $key
     * @param  int  $interval
     *
     * @return array
     */
    protected function getPresetData(array $preset, string $key, int $interval): array
    {
        return [
            'key' => $key,
            'label' => $preset['label'] ?? ucfirst(str_replace('_', ' ', $key)),
            'data' => Arr::flatten(array_fill(0, $interval, $preset['value'])),
            'borderWidth' => $preset['borderWidth'] ?? $this->borderWidth,
            'fill' => $preset['fill'] ?? $this->fill,
            'backgroundColor' => $preset['backgroundColor'] ?? '',
            'borderColor' => $preset['borderColor'] ?? '',
        ];
    }

    /**
     * @param  array  $chartData
     *
     * @return array
     */
    public function chart(array $chartData): array
    {
        $data = ['datasets' => [], 'labels' => []];

        $dates = $this->prepareData($chartData);

        unset($chartData);

        foreach ($this->columnsData as $column => $item) {
            $data['datasets'][] = [
                'key' => $column,
                'label' => $item['label'] ?? ucfirst(str_replace('_', ' ', $column)),
                'data' => array_values($dates[$column]),
                'borderWidth' => $item['borderWidth'] ?? $this->borderWidth,
                'borderColor' => $item['borderColor'] ?? $this->borderColor,
                'fill' => $item['fill'] ?? $this->fill,
                'backgroundColor' => $item['backgroundColor'] ?? '',
            ];
        }
        $data['labels'] = $this->labels;

        array_unshift($data['datasets'], ...$this->getFirstPreset()['datasets']);
        array_push($data['datasets'], ...$this->getLastPreset()['datasets']);

        return $data;
    }
}
