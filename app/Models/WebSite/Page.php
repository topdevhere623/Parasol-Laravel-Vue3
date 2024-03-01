<?php

namespace App\Models\WebSite;

use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'Inactive',
        'active' => 'Active',
    ];

    public const PROTECTED_PAGES = [
        'terms-and-conditions' => 2,
        'privacy-policy' => 1,
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = \Str::slugExtended($model->slug ?: $model->title);
        });
    }

    public function getUrlAttribute(): ?string
    {
        return route('page.show', ['slug' => $this->slug]);
    }

    public static function getProtectedPageUrl($protectedPageAlias): ?string
    {
        return optional(Page::find(Page::PROTECTED_PAGES[$protectedPageAlias]))->url;
    }
}
