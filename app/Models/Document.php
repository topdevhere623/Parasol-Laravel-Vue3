<?php

namespace App\Models;

use App\Casts\FileCast;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends BaseModel
{
    use SoftDeletes;

    public const FILE_CONFIG = [
        'filename' => [
            'path' => 'documents',
        ],
    ];

    protected $casts = [
        'filename' => FileCast::class,
    ];
}
