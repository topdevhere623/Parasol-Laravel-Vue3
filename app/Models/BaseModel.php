<?php

namespace App\Models;

use App\Models\Traits\ColumnLabelTrait;
use App\Models\Traits\ImageDataGettersTrait;
use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Kirschbaum\PowerJoins\PowerJoins;
use ParasolCRM\Activities\ActivityTrait;

class BaseModel extends Model
{
    use PowerJoins;
    use ImageDataGettersTrait;
    use ActivityTrait;
    use ColumnLabelTrait;
    use PivotEventTrait;

    public const FILE_CONFIG = [];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format(config('app.DATETIME_FORMAT'));
    }

    public static function const($const, $value = null)
    {
        $constName = strtoupper(Str::snake(strtolower($const)));

        $foundConst = null;
        if (defined(static::class.'::'.$constName)) {
            $foundConst = constant(static::class.'::'.$constName);
        }

        if (is_array($foundConst) && $value) {
            return array_search($value, $foundConst);
        }
        return $foundConst;
    }
}
