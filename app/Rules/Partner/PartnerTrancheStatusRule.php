<?php

namespace App\Rules\Partner;

use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerTranche;
use Illuminate\Contracts\Validation\InvokableRule;

class PartnerTrancheStatusRule implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (!request('partnerContract') || $value != PartnerTranche::STATUSES['awaiting_first_visit']) {
            return;
        }

        $status = PartnerTranche::getConstOptions('statuses')[$value];
        $partnerContract = PartnerContract::where('id', request('partnerContract'))
            ->first();

        if ($partnerContract->access_type == PartnerContract::ACCESS_TYPES['postpaid']) {
            $type = PartnerContract::getConstOptions('access_types')[PartnerContract::ACCESS_TYPES['postpaid']];
            $fail(
                "The status cannot be \"{$status}\", because contract access type is \"{$type}\""
            );
            return;
        }

        if ($partnerContract->type == PartnerContract::TYPES['addendum']) {
            $type = PartnerContract::getConstOptions('types')[PartnerContract::TYPES['addendum']];
            $fail(
                "The status cannot be \"{$status}\", because contract is of type \"{$type}\""
            );
        }
    }
}
