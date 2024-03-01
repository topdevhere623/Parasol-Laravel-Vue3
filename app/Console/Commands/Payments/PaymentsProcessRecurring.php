<?php

namespace App\Console\Commands\Payments;

use App\Jobs\Payments\PaymentProcessMemberSchedulePaymentJob;
use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Console\Command;

class PaymentsProcessRecurring extends Command
{
    protected $signature = 'payments:process-recurring';
    protected $description = 'Processing members schedule payments';

    public function handle()
    {
        $preQuery = MemberPaymentSchedule::shouldBeCharged();

        $count = $preQuery->count();
        $this->table(['Recurring payments dispatch in process', $count], []);

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $preQuery->chunk(100, function ($items) use ($bar) {
            foreach ($items as $item) {
                PaymentProcessMemberSchedulePaymentJob::dispatch($item);
                $bar->advance();
            }
        });

        $bar->finish();

        echo PHP_EOL;
        return Command::SUCCESS;
    }
}
