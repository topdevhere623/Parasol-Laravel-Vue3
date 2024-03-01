<?php

namespace App\Models\Partner;

use App\Models\BaseModel;
use App\Models\Club\Club;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends BaseModel
{
    use SoftDeletes;
    use Selectable;

    protected string $selectableValue = 'name';

    protected $casts = [
        'current_contract_expiry' => 'date',
        'tranche_expiry' => 'date',
        'is_pooled_access' => 'boolean',
    ];

    public const STATUSES = [
        'active' => 'active',
        'in_progress' => 'in_progress',
        'paused' => 'paused',
        'inactive' => 'inactive',
        'cancelled' => 'cancelled',
        'discontinued' => 'discontinued',
        'negotiation' => 'negotiation',
        'incoming' => 'incoming',
        'pipeline' => 'pipeline',
        'pay_as_you_go' => 'pay_as_you_go',
        'declined' => 'declined',
        'sales_partner' => 'sales_partner',
    ];

    public const SLOTS_TYPES = [
        'revolving' => 'revolving',
        'slots' => 'slots',
    ];

    // Relations

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }

    public function partnerContracts(): HasMany
    {
        return $this->hasMany(PartnerContract::class);
    }

    public function partnerTranches(): HasManyThrough
    {
        return $this->hasManyThrough(
            PartnerTranche::class,
            PartnerContract::class,
            'partner_id',
            'partner_contract_id',
            'id',
            'id'
        );
    }

    public function calculateSlots(): self
    {
        $this->current_contract_expiry = null;
        $this->tranche_expiry = null;
        $this->single_membership_price = 0;
        $this->family_membership_price = 0;
        $this->individual_kid_membership_price = 0;
        $this->purchased_single_membership = 0;
        $this->purchased_family_membership = 0;
        $this->purchased_kid_membership = 0;
        $this->adult_cost_per_visit = 0;
        $this->kid_cost_per_visit = 0;
        $this->adult_slots = 0;
        $this->kid_slots = 0;
        $this->classes_slots = 0;

        $this->partnerContracts()->active()->with('activePartnerTranches')->each(
            function (PartnerContract $partnerContract) {
                if ($partnerContract->type != PartnerContract::TYPES['addendum']) {
                    $this->current_contract_expiry = $partnerContract->expiry_date;
                    $this->slots_type = $partnerContract->slots_type;
                }

                $this->single_membership_price = $this->single_membership_price > 0 ? $this->single_membership_price : $partnerContract->single_membership_price;
                $this->family_membership_price = $this->family_membership_price > 0 ? $this->family_membership_price : $partnerContract->family_membership_price;
                $this->individual_kid_membership_price = $this->individual_kid_membership_price > 0 ? $this->individual_kid_membership_price : $partnerContract->individual_kid_membership_price;

                $this->adult_cost_per_visit += $partnerContract->adult_cost_per_visit;
                $this->kid_cost_per_visit += $partnerContract->kid_cost_per_visit;
                $this->classes_slots += $partnerContract->classes_slots;

                $partnerContract->activePartnerTranches->each(function (PartnerTranche $partnerTranche) {
                    $this->adult_slots += $partnerTranche->adult_slots;
                    $this->kid_slots += $partnerTranche->kid_slots;

                    $this->purchased_single_membership += $partnerTranche->single_membership_count;
                    $this->purchased_family_membership += $partnerTranche->family_membership_count;
                    $this->purchased_kid_membership += $partnerTranche->individual_kid_membership_count;

                    if ($partnerTranche->expiry_date->gt($partnerTranche->tranche_expiry)) {
                        $this->tranche_expiry = $partnerTranche->expiry_date;
                    }
                });
            }
        );

        $this->contract_value = ($this->single_membership_price * $this->purchased_single_membership)
            + ($this->family_membership_price * $this->purchased_family_membership)
            + ($this->individual_kid_membership_price * $this->purchased_kid_membership);

        return $this;
    }

    public function isSlotsTypeRevolving(): bool
    {
        return $this->slots_type == self::SLOTS_TYPES['revolving'];
    }

    public function isSlotsTypeSlots(): bool
    {
        return $this->slots_type == self::SLOTS_TYPES['slots'];
    }
}
