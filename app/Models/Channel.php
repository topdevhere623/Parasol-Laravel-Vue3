<?php

namespace App\Models;

use App\Models\Traits\ActiveStatus;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use Selectable;

    // Constants

    public const MEMBER_REFERRAL_NAME = 'Member Referral';
    public const MEMBER_REFERRAL_ID = 1;

    public const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
        'expired' => 'expired',
    ];

    // Properties

    protected $guarded = ['id'];

    protected string $selectableValue = 'title';

    public static function getIdByTitle(string $title): int
    {
        return self::where('title', $title)->first()->id;
    }

    // Relationships

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }
}
