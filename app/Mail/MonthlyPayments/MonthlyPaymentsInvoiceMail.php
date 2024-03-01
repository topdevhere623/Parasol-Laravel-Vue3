<?php

namespace App\Mail\MonthlyPayments;

use App\Models\Payments\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyPaymentsInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected null|int|Payment $payment;

    public function __construct(int|Payment $payment)
    {
        $this->payment = is_object(
            $payment
        ) ? $payment->id : $payment;
    }

    public function build(): Mailable
    {
        $payment = Payment::where('id', $this->payment)
            ->with('member.plan', 'member.package', 'member.program')
            ->firstOrFail();

        $member = $payment->member;

        $invoice = (object)[
            'full_name' => $member->full_name,
            'program' => $member->program->name,
            'package' => $member->package->title,
            'plan' => $member->plan->title,
            'invoice_number' => $payment->invoice_number,
            'invoice_date' => date_formatter($payment->payment_date),
            'due_date' => date_formatter(now()),
            'total_amount' => $payment->total_amount,
            'subtotal_amount' => $payment->subtotal_amount,
            'vat_amount' => $payment->vat_amount,
            'payment_method' => $payment->paymentMethod->title,
        ];

        return $this->view('emails.monthly-payments.invoice')
            ->with('invoice', $invoice)
            ->subject('adv+ monthly charge receipt')
            ->from('memberships@advplus.ae', config('mail.from.name'));
    }
}
