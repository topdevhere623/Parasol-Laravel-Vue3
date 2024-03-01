<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    use SoftDeletes;

    public const DEFAULT_LATITUDE = 0;
    public const DEFAULT_LONGITUDE = 0;
    public const HOME_COUNTRY_ID = 237;

    /**
     * @var string[]
     */
    public $activityExceptAttributes = ['locatable_id', 'locatable_type'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locatable_id',
        'locatable_type',
        'latitude',
        'longitude',
        'country_id',
        'city_id',
        'area_id',
        'street',
        'building_no',
        'phone',
        'email',
    ];

    /**
     * Get the owning imageable model.
     */
    public function locatable()
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get full address.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return ($this->country && $this->country->iso3 ? $this->country->iso3.', ' : '')
             .($this->area && $this->area->name ? $this->area->name.', ' : '')
             .($this->city && $this->city->name ? $this->city->name : '')
            .' '.$this->street;
    }

    public function labels(): array
    {
        return [
            'country_id' => 'Country',
            'city_id' => 'City',
            'area_id' => 'Area',
        ];
    }

    public function activityRules($value): array
    {
        return [
            'country_id' => fn () => optional(Country::find($value))->country_name,
            'city_id' => fn () => optional(City::find($value))->name,
            'area_id' => fn () => optional(Area::find($value))->name,
        ];
    }
}
