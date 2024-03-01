<?php

namespace App\Actions\SalesQuote;

use App\Models\SalesQuote;

class SalesQuoteCalculateAction
{
    public const ADJUSTMENTS = [
        [
            'month' => 2,
            'percentage' => 250,
            'value' => 1042,
            'days' => 60,
        ],
        [
            'month' => 12,
            'percentage' => 100,
            'value' => 2500,
            'days' => 365,
        ],
        [
            'month' => 6,
            'percentage' => 120,
            'value' => 1500,
            'days' => 183,
        ],
        [
            'month' => 4,
            'percentage' => 150,
            'value' => 1250,
            'days' => 120,
        ],
        [
            'month' => 3,
            'percentage' => 200,
            'value' => 1250,
            'days' => 90,
        ],
        [
            'month' => 1,
            'percentage' => 300,
            'value' => 625,
            'days' => 30,
        ],
        [
            'month' => 0.25,
            'percentage' => 400,
            'value' => 208,
            'days' => 7,
        ],
        [
            'month' => 0.5,
            'percentage' => 350,
            'value' => 365,
            'days' => 15,
        ],
    ];

    public const DISCOUNTS = [
        [
            'moq' => 0,
            'percentage' => 10,
        ],
        [
            'moq' => 20,
            'percentage' => 12.50,
        ],
        [
            'moq' => 100,
            'percentage' => 20,
        ],
        [
            'moq' => 200,
            'percentage' => 22,
        ],
        [
            'moq' => 400,
            'percentage' => 25,
        ],
        [
            'moq' => 600,
            'percentage' => 27,
        ],
    ];

    public const SINGLE_PRICES = [
        [
            'clubs' => 3,
            'price' => 2500,
        ],
        [
            'clubs' => 5,
            'price' => 2700,
        ],
        [
            'clubs' => 10,
            'price' => 2900,
        ],
        [
            'clubs' => 20,
            'price' => 3000,
        ],
        [
            'clubs' => 30,
            'price' => 3100,
        ],
        [
            'clubs' => 31,
            'price' => 3200,
        ],
    ];

    public const COMMISSIONS = [
        [
            'commission' => 100,
            'percentage' => 10,
        ],
        [
            'commission' => 200,
            'percentage' => 12.50,
        ],
        [
            'commission' => 400,
            'percentage' => 15,
        ],
        [
            'commission' => 600,
            'percentage' => 17,
        ],
    ];

    private const TAX_RATE = 0.05;

    private SalesQuote $salesQuote;

    public function __construct(SalesQuote $salesQuote)
    {
        $this->salesQuote = $salesQuote;
    }

    public function handle(array $data = []): array
    {
        $adjustments = $this->salesQuote->json_data['adjustments'] ?? self::ADJUSTMENTS;
        $duration = $data['duration'] ?? $this->salesQuote->duration;
        $durationAdjustment = $this->durationAdjustment($duration, $adjustments);
        $singlePrices = $this->salesQuote->json_data['single_prices'] ?? self::SINGLE_PRICES;
        $clubsCount = $data['clubs_count'] ?? $this->salesQuote->clubs_count;
        $singlePrice = $this->singlePrice($clubsCount, $singlePrices) * $durationAdjustment * $duration / 365;
        $singlesCount = $data['singles_count'] ?? $this->salesQuote->singles_count;
        $familiesCount = $data['families_count'] ?? $this->salesQuote->families_count;
        $totalMembers = $singlesCount + $familiesCount;
        $totalPrice = ($singlesCount + $familiesCount * 2) * $singlePrice;
        $discounts = $this->salesQuote->json_data['discounts'] ?? self::DISCOUNTS;
        $discount = $this->salesDiscount($totalMembers, $discounts);
        $netPrice = $this->discountPrice($totalPrice, $discount);
        $vat = $netPrice * self::TAX_RATE;
        $invoice = $vat + $netPrice;
        $commissions = $this->salesQuote->json_data['commissions'] ?? self::COMMISSIONS;
        $commissionRate = $this->salesCommission($totalMembers, $commissions);
        $commission = $netPrice * $commissionRate;
        $netAdv = $invoice - $commission;
        $singleYearlyDiscountPrice = $this->discountPrice($singlePrice, $discount);
        $singleMonthlyDiscountPrice = $singleYearlyDiscountPrice / 12;
        $singleMonthlyClubDiscountPrice = $this->getPricePerClub($singleMonthlyDiscountPrice, $clubsCount);
        $familyPrice = $singlePrice * 2;
        $familyYearlyDiscountPrice = $this->discountPrice($familyPrice, $discount);
        $familyMonthlyDiscountPrice = $familyYearlyDiscountPrice / 12;
        $familyMonthlyClubDiscountPrice = $this->getPricePerClub($familyMonthlyDiscountPrice, $clubsCount);

        return [
            'duration' => $duration,
            'total_members' => $totalMembers,
            'discount' => round($discount, 2),
            'invoice' => $invoice,
            'commission_rate' => round($commissionRate * 100, 2),
            'commission' => $commission,
            'net_adv' => $netAdv,
            'single_price' => $singlePrice,
            'single_discount' => $singleYearlyDiscountPrice,
            'single_monthly' => $singleMonthlyDiscountPrice,
            'single_monthly_club' => $singleMonthlyClubDiscountPrice,
            'family_price' => $familyPrice,
            'family_discount' => $familyYearlyDiscountPrice,
            'family_monthly' => $familyMonthlyDiscountPrice,
            'family_monthly_club' => $familyMonthlyClubDiscountPrice,
            'total_price' => $totalPrice,
        ];
    }

    private function durationAdjustment(int $days, ?array $adjustments): float|int
    {
        $adjustments = collect($adjustments)->sortByDesc('days');
        $early = false;
        $maxDays = $adjustments->max('days');
        $minDays = $adjustments->min('days');
        if ($days >= $maxDays) {
            $days = $maxDays;
            $early = true;
        } elseif ($days <= $minDays) {
            $days = $minDays;
            $early = true;
        }
        $current = $adjustments->where('days', '<=', $days)->first();
        if ($early) {
            return $current['percentage'] / 100;
        }
        $prev = $adjustments->where('days', '>', $days)->last();
        $l = $prev['days'] - $current['days'];
        $h = ($current['percentage'] / 100) - ($prev['percentage'] / 100);
        $r = (($prev['days'] - $days) / $l) * $h;
        return ($prev['percentage'] / 100) + $r;
    }

    private function singlePrice(int $clubs, array $singlePrices)
    {
        $singlePrices = collect($singlePrices)->sortByDesc('clubs');
        $clubs = $this->putInRange($clubs, $singlePrices->min('clubs'), $singlePrices->max('clubs'));
        $current = $singlePrices->where('clubs', '<=', $clubs)->first();
        return $current['price'];
    }

    private function salesDiscount(int $salesNumber, array $discounts): float|int
    {
        $discounts = collect($discounts)->sortByDesc('moq');
        $salesNumber = $this->putInRange($salesNumber, $discounts->min('moq'), $discounts->max('moq'));
        $current = $discounts->where('moq', '<', $salesNumber)->first();
        return $current ? $current['percentage'] : 100;
    }

    private function discountPrice(float $price, int $discount): float
    {
        return $price * (1 - $discount / 100);
    }

    private function getPricePerClub(float $price, int $clubsCount): float
    {
        return $clubsCount != 0 ? $price / $clubsCount : 0;
    }

    private function getDailyPricePerClub(float $price, int $clubsCount): float
    {
        return $clubsCount != 0 ? $price / 365 / $clubsCount : 0;
    }

    private function salesCommission(int $salesNumber, array $commissions): float|int
    {
        $commissions = collect($commissions)->sortBy('commission');
        if ($salesNumber > $commissions->max('commission')) {
            return 0;
        } elseif ($salesNumber < $commissions->min('commission') + 1) {
            return 0.1;
        }
        $current = $commissions->where('commission', '>=', $salesNumber)->first();
        $prev = $commissions->where('commission', '<', $current['commission'])->first();
        $sum = 0;
        $previousCommission = null;
        $previousArray = $commissions->where('percentage', '<', $current['percentage'])->sortBy('commission');
        foreach ($previousArray as $commission) {
            if ($previousCommission != null) {
                $sum += $previousCommission['commission'] / 100 * ($commission['percentage']);
            } else {
                $sum += $commission['commission'] * ($commission['percentage'] / 100);
            }
            $previousCommission = $commission;
        }
        return ($sum + ($salesNumber - $prev['commission']) * ($current['percentage'] / 100)) / $salesNumber;
    }

    private function putInRange(int $val, int $min, int $max): int
    {
        if ($val > $max) {
            return $max;
        } elseif ($val < $min) {
            return $min;
        }
        return $val;
    }
}
