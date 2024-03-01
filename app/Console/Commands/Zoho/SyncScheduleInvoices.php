<?php

namespace App\Console\Commands\Zoho;

use App\Models\Member\MemberPaymentSchedule;
use App\Services\Zoho\ZohoService;
use Illuminate\Console\Command;

class SyncScheduleInvoices extends Command
{
    protected $signature = 'zoho:sync_recurring_invoices';

    protected $description = 'Sync our recurring payments to zoho books invoices. Создаёт инвойс с "балансом" из оставшихся оплат.';

    public function handle()
    {
        $schedulePayments = MemberPaymentSchedule::query()
            ->with(['member', 'booking'])
            ->active()
            ->get();

        $bar = $this->output->createProgressBar($schedulePayments->count());
        $bar->start();

        /** @var ZohoService $service */
        $service = app(ZohoService::class);

        foreach ($schedulePayments as $schedulePayment) {
            $invoiceData = [
                'reference_number' => $schedulePayment->booking->reference_id,
                'line_items' => [
                    [
                        'item_order' => 1,
                        'item_id' => settings('zoho_membership_item_id'),
                        'rate' => $schedulePayment->calculateRemainAmount(),
                        'name' => 'Membership Fee',
                        'description' => '',
                        'quantity' => '1.00',
                        'discount' => 0,
                        'tax_id' => settings('zoho_tax_id'),
                    ],
                ],
            ];

            $service->createInvoiceBySchedulePayment($schedulePayment->member, $schedulePayment->booking, $invoiceData);

            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
