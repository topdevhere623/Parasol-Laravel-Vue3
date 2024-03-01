<?php

namespace App\Console\Commands\Zoho;

use App\Models\Payments\Payment;
use App\Models\Zoho\ZohoInvoice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchInvoices extends Command
{
    protected $signature = 'zoho:match_invoices';

    protected $description = 'Match invoice id to our payments.';

    public function handle(): int
    {
        DB::enableQueryLog();

        $invoicesQuery = ZohoInvoice::query()
            ->with(['member.payments' => fn (HasMany $q) => $q->where('status', '=', Payment::STATUSES['paid'])])
            ->has('member.payments');

        $invoicesQuery->chunk(200, function (Collection $invoices) {
            foreach ($invoices as $invoice) {
                foreach ($invoice?->member?->payments ?? [] as $payment) {
                    if (
                        $payment->payment_date->isSameDay($invoice->date)
                        && $payment->total_amount === $invoice->total
                    ) {
                        $invoice->payment()->associate($payment);
                        $invoice->save();
                        continue 2;
                    }
                }
            }
        }) ;

        return Command::SUCCESS;
    }
}
