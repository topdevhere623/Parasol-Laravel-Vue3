<?php

namespace App\Models\BlogPost;

use App\Casts\FileCast;
use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends BaseModel
{
    use ActiveStatus;
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    /** @var array */
    protected $guarded = ['id'];

    public const FILE_CONFIG = [
        'cover_image' => [
            'path' => 'blog-post/cover',
            'size' => [[333, 323], [755, 1520], [1500, 600]],
            'action' => ['resize', 'crop', 'png2jpg'],
        ],
        'preview_image' => [
            'path' => 'blog-post/preview',
            'size' => [[350, 350], [700, 700], [900, 900]],
            'action' => ['resize', 'crop', 'png2jpg'],
        ],
        'blogger_photo' => [
            'path' => 'blog-post/blogger-photo',
            'size' => [72],
            'action' => ['resize', 'crop', 'png2jpg'],
        ],
    ];

    public const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
    ];

    protected $casts = [
        'cover_image' => FileCast::class,
        'preview_image' => FileCast::class,
        'blogger_photo' => FileCast::class,
        'blogger_show' => 'boolean',
        'featured' => 'boolean',
        'date' => 'date',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relations

    public function relatedBlogs(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'blog_post_related', 'blog_id', 'related_blog_post_id');
    }

    // Accessors

    public function getDateFormattedAttribute(): string
    {
        return Carbon::parse($this->date)->format('d M Y');
    }

    public function getLinkAttribute(): string
    {
        return route('blog-post', ['blog' => $this->slug]);
    }

    public function getReadingTimeAttribute(): string
    {
        return (int)(strlen(strip_tags($this->text)) / 250);
    }

    // Local scopes

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeNotFeatured(Builder $query): Builder
    {
        return $query->where('featured', false);
    }
}
