<?php

namespace App\Observers;

use App\Models\Payments\PaymentTransaction;

class PaymentTransactionObserver
{
    public function creating(PaymentTransaction $model)
    {
        $model->uuid = \Str::orderedUuid()->toString();
    }

    public function created(PaymentTransaction $model)
    {
        $this->updatePaymentPaymentMethod($model);
    }

    public function saving(PaymentTransaction $model): void
    {
        if ($model->type == PaymentTransaction::TYPES['refund']) {
            $model->amount = abs($model->amount) * -1;
        }

        // If transaction status changed from pending to success
        if ($model->isDirty('status') && $model->getOriginal('status') == PaymentTransaction::STATUSES['pending']) {
            $this->updatePaymentPaymentMethod($model);
        }
    }

    protected function updatePaymentPaymentMethod(PaymentTransaction $model)
    {
        if ($model->status == PaymentTransaction::STATUSES['success'] && $payment = $model->payment) {
            $payment->paymentMethod()
                ->associate($model->paymentMethod)
                ->save();
        }
    }
}
