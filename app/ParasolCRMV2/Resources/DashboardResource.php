<?php

namespace App\ParasolCRMV2\Resources;

// use App\Models\Member\Child;
use App\Models\Member\Member;
// use App\Models\Member\Partner;
use ParasolCRMV2\Charts\PieChart;
use ParasolCRMV2\ResourceScheme;

class DashboardResource extends ResourceScheme
{
    public $title = 'Dashboard';

    public static $model = Member::class;

    /**
     * @return array
     */
    public function charts(): array
    {
        //        $now = now()->startOfDay();
        //        $year18 = $now->clone()->subRealYears(18);
        //        $year25 = $now->clone()->subRealYears(25);
        //        $year30 = $now->clone()->subRealYears(30);
        //        $year45 = $now->clone()->subRealYears(45);
        //        $year50 = $now->clone()->subRealYears(50);

        return [
            // Gender
            //            PieChart::make([
            //                'male' => [
            //                    'label' => 'Male',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'male'],
            //                    'backgroundColor' => '#00a4de',
            //                    'hoverBackgroundColor' => 'rgba(92, 211, 255, 1.1)'
            //                ],
            //                'female' => [
            //                    'label' => 'Female',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'female'],
            //                    'backgroundColor' => '#4cd964',
            //                    'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                ],
            //            ],
            //            )
            //                ->name('memberGender')
            //                ->borderWidth(1)
            //                ->borderColor('#fff'),
            //
            //            PieChart::make([
            //                'male' => [
            //                    'label' => 'Male',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'male'],
            //                    'backgroundColor' => '#00a4de',
            //                    'hoverBackgroundColor' => 'rgba(92, 211, 255, 1.1)'
            //                ],
            //                'female' => [
            //                    'label' => 'Female',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'female'],
            //                    'backgroundColor' => '#4cd964',
            //                    'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                ],
            //            ],
            //                Partner::class
            //            )
            //                ->name('partnerGender')
            //                ->borderWidth(1)
            //                ->borderColor('#fff'),
            //
            //            PieChart::make([
            //                'male' => [
            //                    'label' => 'Male',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'male'],
            //                    'backgroundColor' => '#00a4de',
            //                    'hoverBackgroundColor' => 'rgba(92, 211, 255, 1.1)'
            //                ],
            //                'female' => [
            //                    'label' => 'Female',
            //                    'action' => 'count',
            //                    'case' => ['gender', '=', 'female'],
            //                    'backgroundColor' => '#4cd964',
            //                    'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                ],
            //            ],
            //                Child::class
            //            )
            //                ->name('childGender'),
            //
            //            /* Years */
            //            PieChart::make([
            //                'year1_17' => [
            //                    'label' => '1-17',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', '>', $year18->subDay()],
            //                    'backgroundColor' => '#00a4de',
            //                    'hoverBackgroundColor' => 'rgba(0, 164, 222, 1.1)'
            //                ],
            //                'year18_24' => [
            //                    'label' => '18-24',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year25->subDay(), $year18],
            //                    'backgroundColor' => '#4cd964',
            //                    'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                ],
            //                'year25_30' => [
            //                    'label' => '25-30',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year30->subDay(), $year25],
            //                    'backgroundColor' => '#FF6384',
            //                    'hoverBackgroundColor' => 'rgba(255, 99, 132, 1.1)'
            //                ],
            //                'year31_45' => [
            //                    'label' => '31-45',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year45->subDay(), $year30],
            //                    'backgroundColor' => '#ffcc00',
            //                    'hoverBackgroundColor' => 'rgba(255, 204, 0, 1.1)'
            //                ],
            //                'year46_50' => [
            //                    'label' => '46-50',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year50->subDay(), $year45],
            //                    'backgroundColor' => '#8c61f9',
            //                    'hoverBackgroundColor' => 'rgba(140, 97, 249, 1.1)'
            //                ],
            //                'year_older_50' => [
            //                    'label' => '50 +',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', '<', $year50],
            //                    'backgroundColor' => '#e7534b',
            //                    'hoverBackgroundColor' => 'rgba(231, 83, 75, 1.1)'
            //                ],
            //            ],
            //                Member::class
            //            )
            //                ->name('memberDob')
            //                ->borderWidth(1)
            //                ->borderColor('#fff'),
            //
            //            PieChart::make([
            //                'year1_17' => [
            //                    'label' => '1-17',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', '>', $year18],
            //                    'backgroundColor' => '#00a4de',
            //                    'hoverBackgroundColor' => 'rgba(0, 164, 222, 1.1)'
            //                ],
            //                'year18_24' => [
            //                    'label' => '18-24',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year25->subDay(), $year18],
            //                    'backgroundColor' => '#4cd964',
            //                    'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                ],
            //                'year25_30' => [
            //                    'label' => '25-30',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year30->subDay(), $year25],
            //                    'backgroundColor' => '#FF6384',
            //                    'hoverBackgroundColor' => 'rgba(255, 99, 132, 1.1)'
            //                ],
            //                'year31_45' => [
            //                    'label' => '31-45',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year45->subDay(), $year30],
            //                    'backgroundColor' => '#ffcc00',
            //                    'hoverBackgroundColor' => 'rgba(255, 204, 0, 1.1)'
            //                ],
            //                'year46_50' => [
            //                    'label' => '46-50',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', 'BETWEEN', $year50->subDay(), $year45],
            //                    'backgroundColor' => '#8c61f9',
            //                    'hoverBackgroundColor' => 'rgba(140, 97, 249, 1.1)'
            //                ],
            //                'year_older_50' => [
            //                    'label' => '50 +',
            //                    'action' => 'count',
            //                    'case' => ['date_of_birth', '<', $year50],
            //                    'backgroundColor' => '#e7534b',
            //                    'hoverBackgroundColor' => 'rgba(231, 83, 75, 1.1)'
            //                ],
            //            ],
            //                Partner::class
            //            )
            //                ->name('partnerDob'),
            //
            //            PieChart::make([
            //                    'year1_17' => [
            //                        'label' => '1-17',
            //                        'action' => 'count',
            //                        'case' => ['date_of_birth', '<', $year18],
            //                        'backgroundColor' => '#00a4de',
            //                        'hoverBackgroundColor' => '#rgba(0, 164, 222, 1.1)'
            //                    ],
            //                    'year18_24' => [
            //                        'label' => '18-24',
            //                        'action' => 'count',
            //                        'case' => ['date_of_birth', 'BETWEEN', $year25->subDay(), $year18],
            //                        'backgroundColor' => '#4cd964',
            //                        'hoverBackgroundColor' => 'rgba(76, 217, 100, 1.1)'
            //                    ],
            //                ],
            //                Child::class
            //            )
            //                ->name('childDob')
        ];
    }
}
