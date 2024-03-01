<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Traits\HasMemberRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipSource extends BaseModel
{
    use SoftDeletes;
    use HasMemberRelation;

    protected $fillable = ['title'];

    public function scopeSort(Builder $query): Builder
    {
        return $query->orderBy('sort');
    }

    public function scopeDisplayOnBooking(Builder $query): Builder
    {
        return $query->where($this->getTable().'.display_on_booking', true);
    }

    public static function getOrCreateMembershipSource(?int $id = null, ?string $newTitle = null): ?self
    {
        if ($membershipSource = self::whereId($id)->first()) {
            return $membershipSource;
        }
        if ($newTitle) {
            return self::firstOrCreate([
                'title' => trim($newTitle),
            ]);
        }
        return null;
    }
}
