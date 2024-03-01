<?php

namespace App\Models\Zoho;

use App\Models\Booking;
use App\Models\Member\MemberPrimary;
use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ZohoInvoice extends Model
{
    protected $table = 'zoho_invoices';

    public $timestamps = false;

    protected $guarded = [];

    public function member(): HasOneThrough
    {
        return $this->hasOneThrough(
            MemberPrimary::class,
            ZohoCustomer::class,
            'contact_id',
            'id',
            'customer_id',
            'member_id'
        );
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'zoho_invoice_id', 'id');
    }

    /**
     * Ссылка на админку в зохо
     */
    public function urlToAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf('https://books.zoho.com/app/%s#/invoices/%s', settings('zoho_organization_id'), $this->id),
        );
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}
