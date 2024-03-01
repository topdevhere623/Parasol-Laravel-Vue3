<?php

namespace App\Mail\MonthlyPayments;

use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyPaymentChargeFailedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected array $emailSubjects = [
        'first' => 'adv+ payment issue, is everything ok?',
        'second' => 'Hello, hello! adv+ following up',
        'third' => 'Your adv+ membership is on hold',
    ];

    protected object $data;

    protected string $emailView;

    public function __construct(MemberPaymentSchedule $memberPaymentSchedule, string $emailView)
    {
        $this->emailView = $emailView;

        $member = $memberPaymentSchedule->member;
        $this->data = (object)[
            'first_name' => $member->first_name,
            'last4_digits' => $memberPaymentSchedule->card_last4_digits,
            'payment_amount' => 'AED '.money_formatter($memberPaymentSchedule->calculateChargeAmount()),
            'member_id' => $member->member_id,
            'member_portal_url' => \URL::route(
                'monthly-payments-card-change',
                ['token' => $memberPaymentSchedule->card_change_auth_token]
            ),
        ];
    }

    public function build(): Mailable
    {
        return $this->view("emails.monthly-payments.fails.{$this->emailView}")
            ->with('data', $this->data)
            ->subject($this->emailSubjects[$this->emailView])
            ->from('memberships@advplus.ae', config('mail.from.name'));
    }
}
