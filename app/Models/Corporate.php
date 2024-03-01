<?php

namespace App\Models;

use App\Models\Traits\HasMemberRelation;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Corporate extends BaseModel
{
    use SoftDeletes;
    use Selectable;

    use HasMemberRelation;

    protected string $selectableValue = 'title';

    protected $fillable = ['title'];

    public static function firstOrCreateByTitle(string $title): self
    {
        return self::firstOrCreate([
            'title' => trim($title),
        ]);
    }

    public function scopeShowOnMain(Builder $builder): Builder
    {
        return $builder->whereShowOnMain(true);
    }
}
