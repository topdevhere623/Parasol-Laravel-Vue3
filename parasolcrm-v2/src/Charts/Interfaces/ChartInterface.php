<?php

namespace ParasolCRMV2\Charts\Interfaces;

interface ChartInterface
{
    /**
     * @param array $chartData
     * @return array
     */
    public function chart(array $chartData): array;
}
