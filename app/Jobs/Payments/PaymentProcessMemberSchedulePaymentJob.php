<?php

namespace App\Jobs\Payments;

use App\Actions\ProcessMemberSchedulePaymentAction;
use App\Exceptions\Payments\MakePaymentException;
use App\Mail\MonthlyPayments\MonthlyPaymentChargeFailedMail;
use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PaymentProcessMemberSchedulePaymentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    private int $memberPaymentScheduleId;

    // Relation day of month to email template
    protected array $paymentFailedEmails = [
        1 => 'first',
        3 => 'second',
        7 => 'third',
    ];

    public function __construct($memberPaymentSchedule)
    {
        $this->memberPaymentScheduleId = is_object(
            $memberPaymentSchedule
        ) ? $memberPaymentSchedule->id : $memberPaymentSchedule;
    }

    public function handle(ProcessMemberSchedulePaymentAction $paymentAction): void
    {
        $memberPaymentSchedule = MemberPaymentSchedule::where('id', $this->memberPaymentScheduleId)
            ->shouldBeCharged()
            ->with('member')
            ->firstOrFail();

        $member = $memberPaymentSchedule->member;

        try {
            $paymentAction->handle($memberPaymentSchedule);

            if ($member->end_date->isCurrentMonth()) {
                $memberPaymentSchedule->markAsCompleted()
                    ->save();
            }
        } catch (MakePaymentException) {
            if (!$memberPaymentSchedule->card_change_auth_token) {
                $memberPaymentSchedule->generateCardChangeAuthToken()
                    ->save();
            }

            if (now()->day == 7) {
                $member->setFailedPaymentStatus()->save();
                $member->activePartner?->setFailedPaymentStatus()->save();
                foreach ($member->activeJuniors as $junior) {
                    $junior->setFailedPaymentStatus()->save();
                }

                $memberPaymentSchedule->markAsFailed()
                    ->save();
            }

            if (!key_exists(now()->day, $this->paymentFailedEmails)) {
                report(
                    'PaymentProcessMemberSchedulePaymentJob id: '.$this->memberPaymentScheduleId.' failure email send failed'
                );
                return;
            }

            \Mail::to($memberPaymentSchedule->member->email)
                ->send(
                    new MonthlyPaymentChargeFailedMail($memberPaymentSchedule, $this->paymentFailedEmails[now()->day])
                );
        }
    }
}
