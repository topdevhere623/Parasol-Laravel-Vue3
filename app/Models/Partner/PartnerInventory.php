<?php

namespace App\Models\Partner;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerInventory extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'airtable_id',
        'model',
        'serial_number',
        'purchase_date',
        'installation_date',
        'returned_to_parasol',
        'price',
        'login_details',
        'partner_id',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function files()
    {
        return $this->hasMany(PartnerInventoryFile::class);
    }
}
