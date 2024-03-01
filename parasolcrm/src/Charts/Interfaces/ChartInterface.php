<?php

namespace ParasolCRM\Charts\Interfaces;

interface ChartInterface
{
    /**
     * @param array $chartData
     * @return array
     */
    public function chart(array $chartData): array;
}
