<?php

namespace App\Jobs\Zoho;

use App\Models\Payments\Payment;
use App\Services\Zoho\ZohoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 5;

    public $timeout = 120;

    public Payment $payment;
    private bool $isMonthlyInvoice;

    public function __construct(Payment $payment, bool $isMonthlyInvoice = false)
    {
        $this->payment = $payment->withoutRelations();
        $this->isMonthlyInvoice = $isMonthlyInvoice;
    }

    public function handle()
    {
        if (!app()->isProduction()) {
            return;
        }

        /** @var ZohoService $zoho */
        $zoho = app(ZohoService::class);

        //        if (!$zoho->isAvailable()) {
        //            return;
        //        }

        $zohoInvoice = $zoho->createInvoiceByPayment($this->payment, $this->isMonthlyInvoice);

        if ($this->isMonthlyInvoice === true) {
            CreatePaymentJob::dispatch($this->payment);
        }
    }
}
