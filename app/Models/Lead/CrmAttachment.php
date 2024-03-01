<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Models\Traits\ImageDataGettersTrait;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmAttachment extends BaseModel
{
    use ImageDataGettersTrait;
    use SoftDeletes;
    use UuidOnCreating;

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'crm_attachments',
        ],
    ];

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    // Mutators & Accessors

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['url'] ?? \URL::uploads(self::getFilePath('file').'/original/'.$attributes['name']),
        );
    }
}
