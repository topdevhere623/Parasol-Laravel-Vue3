<?php

namespace App\Rules\Partner;

use App\Models\Partner\PartnerContract;
use Illuminate\Contracts\Validation\InvokableRule;

class PartnerContractTypeRule implements InvokableRule
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
        if ($value == PartnerContract::TYPES['addendum']) {
            return;
        }

        $exists = PartnerContract::where(
            'billing_period',
            PartnerContract::billingPeriodToYear(request('billing_period'))
        )
            ->where('partner_id', request('partner'))
            ->where('type', request('type'))
            ->where('id', '!=', request()->route('id'))
            ->exists();

        if (!$exists) {
            return;
        }

        $type = PartnerContract::getConstOptions('types')[$value];
        $fail(
            "There should not be more then one partner contract of type \"{$type}\" with given partner and billing period."
        );
    }
}
