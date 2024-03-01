<?php

namespace App\Jobs\PaymentMethods;

use App\Actions\Booking\TabbyResolveBookingPaymentAction;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CheckTabbyPendingTransactionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tabbyResolvePaymentBookingAction = new TabbyResolveBookingPaymentAction();
        PaymentTransaction::pending()
            ->whereIn('payment_method_id', [PaymentMethod::TABBY_THREE_PAYMENT_ID, PaymentMethod::TABBY_SIX_PAYMENT_ID])
            ->where('created_at', '<=', now()->subMinutes(0))
            ->where('created_at', '>=', now()->subMinutes(30))
            ->where('type', PaymentTransaction::TYPES['capture'])
            ->where('status', PaymentTransaction::STATUSES['pending'])
            ->whereNotNull('remote_id')
            ->chunkById(
                100,
                fn (Collection $items) => $items->each(
                    fn (PaymentTransaction $paymentTransaction) => $tabbyResolvePaymentBookingAction->handle(
                        $paymentTransaction
                    )
                )
            );
    }
}
