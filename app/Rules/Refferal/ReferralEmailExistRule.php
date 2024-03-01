<?php

namespace App\Rules\Refferal;

use App\Repositories\MemberRepository;
use Illuminate\Contracts\Validation\Rule;

class ReferralEmailExistRule implements Rule
{
    /** @var string */
    protected string $message = '';

    public function passes($attribute, $value): bool
    {
        $repository = new MemberRepository();
        if ($repository->checkExistReferralByEmail($value)) {
            $this->message = "You have already added a referral with this email: {$value}.";
            return false;
        }
        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}
