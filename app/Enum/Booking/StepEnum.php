<?php

namespace App\Enum\Booking;

use Exception;

enum StepEnum: string
{
    case Payment = 'payment';
    case MembershipDetails = 'membership_details';
    case Completed = 'completed';
    case Default = 'default';

    /**
     * @deprecated Шаг не будет использоваться.
     */
    case BillingDetails = 'billing_details';

    public function getOldValue(): int
    {
        return match ($this->value) {
            self::Default->value => 1,
            self::Payment->value => 2,
            self::BillingDetails->value => 3,
            self::MembershipDetails->value => 3,
            self::Completed->value => 4,
            default => throw new Exception('Unexpected match value'),
        };
    }

    public static function afterPaymentSteps()
    {
        return [StepEnum::BillingDetails, StepEnum::MembershipDetails, StepEnum::Completed];
    }

    public function getPreviousStep(): ?self
    {
        return match ($this->value) {
            self::Payment->value => self::Default,
            self::MembershipDetails->value => self::Payment,
            self::Completed->value => self::MembershipDetails,
            default => null
        };
    }
}
