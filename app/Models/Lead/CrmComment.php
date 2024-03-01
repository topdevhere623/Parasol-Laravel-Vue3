<?php

namespace App\Models\Lead;

use App\Casts\JsonCast;
use App\Models\BackofficeUser;
use App\Models\BaseModel;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmComment extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    /** @var array */
    protected $casts = [
        'extended_info' => JsonCast::class,
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function backofficeUser(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class);
    }

    public function crmActivity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class);
    }

    public function crmAttachments(): MorphMany
    {
        return $this->morphMany(CrmAttachment::class, 'attachable');
    }

    public function actionItem(): HasOne
    {
        return $this->hasOne(CrmEmail::class);
    }

    public function scopeSort($query): Builder
    {
        return $query->orderBy('is_pinned', 'desc')
            ->latest();
    }

    public function mentions(): array
    {
        $mentions = [];
        preg_match_all('/%\{(.*?)}/', $this->raw_content, $matches);
        foreach ($matches[1] as $match) {
            $parts = explode(',', $match);
            $mention = [];
            foreach ($parts as $part) {
                $properties = explode(':', $part);
                if (count($properties) > 1) {
                    $mention[trim(trim($properties[0]), '"')] = trim(trim($properties[1]), '"');
                }
            }

            if (count($mention)) {
                $mentions[] = $mention;
            }
        }

        return $mentions;
    }
}
