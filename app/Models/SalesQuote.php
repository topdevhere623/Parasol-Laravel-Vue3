<?php

namespace App\Models;

use App\Casts\JsonCast;
use App\Models\Traits\ColumnLabelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuote extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use ColumnLabelTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'json_data' => JsonCast::class,
        'display_monthly_value' => 'boolean',
        'display_daily_per_club' => 'boolean',
    ];

    public const MONTHS_TO_DAYS = [
        '0.25' => 7,
        '0.5' => 15,
        1 => 30,
        2 => 60,
        3 => 90,
        4 => 120,
        6 => 183,
        12 => 365,
    ];

    public function getPdfFilePath(): string
    {
        return "pdfs/quote-{$this->uuid}.pdf";
    }

    public static function monthsToDays($months): int
    {
        return self::MONTHS_TO_DAYS[$months];
    }

    public static function daysToMonths($days): float
    {
        return array_flip(self::MONTHS_TO_DAYS)[$days];
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class, 'sales_person_id', 'id');
    }
}
