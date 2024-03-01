<?php

namespace ParasolCRMV2\Charts;

class PieChart extends Chart
{
    /** @var string */
    public string $name = 'pieChart';

    /**
     * @param array $chartData
     * @return array
     */
    public function chart(array $chartData): array
    {
        $data = [];

        $dates = $this->prepareData($chartData);
        unset($chartData);

        $labels = [];
        $hoverBackgroundColors = [];
        $backgroundColors = [];
        $borderColors = [];
        $borderWidth = [];
        $values = [];

        foreach ($this->columnsData as $column => $item) {
            array_push($labels, $item['label'] ?? ucfirst(str_replace('_', ' ', $column)));
            array_push($hoverBackgroundColors, $item['hoverBackgroundColor'] ?? '');
            array_push($backgroundColors, $item['backgroundColor'] ?? '');
            array_push($borderColors, $item['borderColor'] ?? $this->borderColor);
            array_push($borderWidth, $item['borderWidth'] ?? $this->borderWidth);

            $items = array_values($dates[$column]);
            $value = array_sum($items);

            if (strtoupper($item['action']) === 'AVG') {
                if (count($items)) {
                    $value = $value / count($items);
                }
            }
            array_push($values, $value);
        }

        $data['datasets'][] = [
            'keys' => array_keys($this->columnsData),
            'data' => $values,
            'hoverBackgroundColor' => $hoverBackgroundColors,
            'backgroundColor' => $backgroundColors,
            'borderColor' => $borderColors,
            'borderWidth' => $borderWidth,
        ];
        $data['labels'] = $labels;

        return $data;
    }
}
