<?php

namespace App\Actions\Payment\Mamo;

use App\Jobs\Zoho\CreatePaymentJob;
use App\Mail\MonthlyPayments\MonthlyPaymentMembershipActivatedMail;
use App\Mail\MonthlyPayments\MonthlyPaymentsInvoiceMail;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\PaymentMamoLink;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use URL;

class PaymentMamoResolveAttachCard
{
    public function handle(
        PaymentMamoLink $paymentMamoLink,
        object $mamoPaymentResponse,
        string $remoteId,
        string $transactionStatus
    ): RedirectResponse {
        /** @var MemberPaymentSchedule $memberPaymentSchedule */
        $memberPaymentSchedule = $paymentMamoLink->payable;
        $responsePaymentMethod = $mamoPaymentResponse->payment_method;

        $cardDetails = $this->getCardDetails($responsePaymentMethod);

        if ($memberPaymentSchedule->calculateChargeAmount() != 0) {
            $paymentMonth = now()->format('nY');
            $paymentMethod = PaymentMethod::where('code', 'mamo_monthly')->first()->id;
            $payment = $memberPaymentSchedule
                ->payments()
                ->wherePivot('payment_month', $paymentMonth)
                ->first();

            $paymentTransaction = PaymentTransaction::make([
                'remote_id' => $remoteId,
                'status' => $transactionStatus,
                'type' => PaymentTransaction::TYPES['capture'],
                'amount' => $mamoPaymentResponse->amount,
                'description' => $mamoPaymentResponse->error_message ? $mamoPaymentResponse->error_code.': '.$mamoPaymentResponse->error_message : null,
            ]);

            $paymentTransaction->payment()->associate($payment);
            $paymentTransaction->paymentMethod()->associate($paymentMethod);
            $paymentTransaction->save();
            $paymentTransaction->attachResponse(json_encode($mamoPaymentResponse));

            if ($transactionStatus != PaymentTransaction::STATUSES['success']) {
                $payment->paymentMethod()->associate($paymentMethod);
                $payment->markAsFailed()
                    ->save();
                return redirect(URL::member('personal/payment-details?status=failed'));
            }

            $payment->paymentMethod()->associate($paymentMethod);
            $payment->markAsPaid()
                ->save();

            $paymentMamoLink->is_active = false;
            $paymentMamoLink->save();

            CreatePaymentJob::dispatch($payment);

            $memberPaymentSchedule->card_change_auth_token = null;
            $memberPaymentSchedule->markAsActive();
            $memberPaymentSchedule->charge_date = $memberPaymentSchedule->charge_date->addMonths(
                $memberPaymentSchedule->calculateOverdueMonths()
            );
            $memberPaymentSchedule->save();

            $member = $memberPaymentSchedule->member;
            \Mail::to($member->email)
                ->send(new MonthlyPaymentsInvoiceMail($payment));

            if ($member->membership_status == Member::MEMBERSHIP_STATUSES['payment_defaulted_on_hold']) {
                $member->membership_status = $member->end_date->isFuture(
                ) ? Member::MEMBERSHIP_STATUSES['active'] : Member::MEMBERSHIP_STATUSES['expired'];
                $member->save();

                \Mail::to($member->email)
                    ->send(new MonthlyPaymentMembershipActivatedMail($memberPaymentSchedule));
            }
        }

        if ($transactionStatus != PaymentTransaction::STATUSES['success']) {
            return redirect(URL::member('personal/payment-details?status=failed'));
        }

        $this->updateMemberPaymentSchedule($memberPaymentSchedule, $cardDetails);

        $this->deactivatePaymentMamoLink($paymentMamoLink);

        return redirect(URL::member('personal/payment-details?status=success'));
    }

    private function getCardDetails($responsePaymentMethod): array
    {
        return [
            'card_token' => $responsePaymentMethod->card_id,
            'last4' => $responsePaymentMethod->card_last4,
            'scheme' => str_contains(strtolower($responsePaymentMethod->type), 'visa') ? 'visa' : 'mastercard',
        ];
    }

    private function updateMemberPaymentSchedule(MemberPaymentSchedule $memberPaymentSchedule, array $cardDetails): void
    {
        $memberPaymentSchedule->update([
            'card_token' => $cardDetails['card_token'],
            'card_last4_digits' => $cardDetails['last4'],
            'card_scheme' => $cardDetails['scheme'],
            'card_expiry_date' => null,
            'card_status' => MemberPaymentSchedule::CARD_STATUS['active'],
            'payment_method_id' => PaymentMethod::where('code', 'mamo_monthly')->first()->id,
        ]);
    }

    private function deactivatePaymentMamoLink(PaymentMamoLink $paymentMamoLink): void
    {
        $paymentMamoLink->is_active = false;
        $paymentMamoLink->save();
    }
}
