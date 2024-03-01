<?php

namespace App\Observers;

use App\Models\Payments\Payment;

class PaymentObserver
{
    public function creating(Payment $model)
    {
        $model->payment_date ??= now();
    }

    public function created(Payment $model)
    {
        if (!$model->invoice_number) {
            $model->invoice_number = 'INV-'.str_pad($model->id, 5, '0', STR_PAD_LEFT);
        }

        $model->reference_id ??= $model->invoice_number;
        $model->save();
    }

    public function saving(Payment $model): void
    {
        $model->calculateVatAndTotal();
        if ($model->isDirty('refund_amount')) {
            $model->status
                = $model->total_amount - $model->refund_amount == 0 ? Payment::STATUSES['refunded'] : Payment::STATUSES['partial_refunded'];
        }
    }

    public function deleted(Payment $model): void
    {
        $model->paymentTransactions->each(function ($item) {
            $item->delete();
        });
    }
}
