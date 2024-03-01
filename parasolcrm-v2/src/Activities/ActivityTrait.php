<?php

namespace ParasolCRMV2\Activities;

use ParasolCRMV2\Activities\Facades\Activity;

/**
 * Trait ActivityTrait
 *
 * $activityActive bool
 * $activityAttributes array
 * $activityExceptAttributes array
 *
 * @package ParasolCRMV2\Activities
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
