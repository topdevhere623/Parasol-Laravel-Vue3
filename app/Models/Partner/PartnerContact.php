<?php

namespace App\Models\Partner;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerContact extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'airtable_id',
        'contact',
        'phone',
        'email',
        'job_role',
        'type',
        'notes',
    ];

    public const TYPES = [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
    ];

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class);
    }
}
