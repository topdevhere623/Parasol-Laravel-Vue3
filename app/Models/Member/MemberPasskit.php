<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPasskit extends BaseModel
{
    use SoftDeletes;

    public const STATUSES = [
        'PASS_ISSUED' => 'PASS_ISSUED',
        'PASS_INSTALLED' => 'PASS_INSTALLED',
        'PASS_UNINSTALLED' => 'PASS_UNINSTALLED',
        'PASS_INVALIDATED ' => 'PASS_INVALIDATED',
    ];

    protected $fillable = [
        'passkit_id',
        'status',
        'has_apple_installed',
        'has_google_installed',
        'has_apple_uninstalled',
        'has_google_uninstalled',
        'member_id',
    ];

    protected $visible = [
        'passkit_id',
        'pass_status',
        'pass_url',
        'has_apple_installed',
        'has_google_installed',
        'has_apple_uninstalled',
        'has_google_uninstalled',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)
            ->withTrashed();
    }

    public function getPassUrlAttribute(): string
    {
        return config('services.passkit.pass_url').$this->passkit_id;
    }

    public function getPassStatusAttribute(): string
    {
        return \Str::of($this->status)->replace('_', ' ')->title();
    }

    public function clearState(): self
    {
        $this->fill([
            'status' => static::STATUSES['PASS_ISSUED'],
            'has_apple_installed' => false,
            'has_google_installed' => false,
            'has_apple_uninstalled' => false,
            'has_google_uninstalled' => false,
        ]);

        return $this;
    }
}
