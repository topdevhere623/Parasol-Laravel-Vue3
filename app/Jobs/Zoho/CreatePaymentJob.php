<?php

namespace App\Jobs\Zoho;

use App\Models\Payments\Payment;
use App\Services\Zoho\ZohoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePaymentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 5;

    public $timeout = 120;

    public Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment->withoutRelations();
    }

    public function handle()
    {
        /** @var ZohoService $zoho */
        $zoho = app(ZohoService::class);

        if (!$zoho->isAvailable()) {
            return;
        }

        $zohoPayment = $zoho->createPaymentByPayment($this->payment);
    }
}
