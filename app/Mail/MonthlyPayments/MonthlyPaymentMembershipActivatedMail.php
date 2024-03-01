<?php

namespace App\Mail\MonthlyPayments;

use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyPaymentMembershipActivatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected object $data;

    public function __construct(MemberPaymentSchedule $memberPaymentSchedule)
    {
        $member = $memberPaymentSchedule->member;

        $this->data = (object)[
            'first_name' => $member->first_name,
            'member_id' => $member->member_id,
        ];
    }

    public function build(): Mailable
    {
        return $this->view('emails.monthly-payments.membership-activated')
            ->with('data', $this->data)
            ->subject('Success! Your membership is reactivated')
            ->from('memberships@advplus.ae', config('mail.from.name'));
    }
}
