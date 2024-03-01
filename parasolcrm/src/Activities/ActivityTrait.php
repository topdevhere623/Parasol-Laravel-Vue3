<?php

namespace ParasolCRM\Activities;

use ParasolCRM\Activities\Facades\Activity;

/**
 * Trait ActivityTrait
 *
 * $activityActive bool
 * $activityAttributes array
 * $activityExceptAttributes array
 *
 * @package ParasolCRM\Activities
 */
trait ActivityTrait
{
    public $activityActive = true;

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->wasRecentlyCreated ? Activity::created($model) : Activity::updated($model);
        });

        static::deleted(function ($model) {
            Activity::deleted($model);
        });
    }

    public function activityRules($value): array
    {
        return [];
    }
}
