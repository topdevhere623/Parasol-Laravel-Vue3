<?php

namespace App\Console\Commands\Payments;

use App\Actions\Booking\BookingPriceCalculateAction;
use App\Models\Booking;
use App\Models\Member\MemberPaymentSchedule;
use DB;
use Illuminate\Console\Command;

class PaymentsRecalculateProgramCommissionsRecurring extends Command
{
    protected $signature = 'payments:recalculate-program-commissions';
    protected $description = 'Recalculate 3rd party commissions for all bookings, payments, scheduled payments. Tables timestamps will be not updated.';

    public function handle(BookingPriceCalculateAction $action)
    {
        if (!$this->confirm(
            'All 3rd party commissions will be recalculated, there is no way to rollback. Do you wish to continue?'
        )) {
            return Command::FAILURE;
        }

        $preQuery = Booking::query();
        // ->where('plan_id', 80);
        // ->where('id', 1785)
        $count = $preQuery->count();
        $bar = $this->output->createProgressBar($count);
        $this->line('Calculation in process:');
        $bar->start();
        $preQuery->with([
            'plan' => fn ($query) => $query->withTrashed(),
            'payment' => fn ($query) => $query->withTrashed(),
        ])
            ->chunkById(100, function ($bookings) use ($action, $bar) {
                /** @var Booking $booking */
                foreach ($bookings as $booking) {
                    $booking->gift_card_amount = 0;
                    $booking = $action->handle($booking);

                    $booking->total_third_party_commission_amount = array_sum([
                        $booking->plan_third_party_commission_amount,
                        $booking->extra_child_third_party_commission_amount,
                        $booking->extra_junior_third_party_commission_amount,
                    ]);

                    $booking->total_third_party_commission_amount = $booking->total_third_party_commission_amount < 1
                        ? 0
                        : $booking->total_third_party_commission_amount;

                    $data = $booking->only([
                        'plan_third_party_commission_amount',
                        'extra_child_third_party_commission_amount',
                        'extra_junior_third_party_commission_amount',
                        'total_third_party_commission_amount',
                    ]);

                    $booking->refresh();
                    $booking->timestamps = false;
                    $booking->updateQuietly($data);

                    $bar->advance();

                    if (!$booking->total_third_party_commission_amount) {
                        continue;
                    }

                    if ($memberPaymentSchedule = MemberPaymentSchedule::where(
                        'booking_id',
                        $booking->id
                    )->first()) {
                        $programCommissionMonthlyPayment = MemberPaymentSchedule::calculate(
                            $booking->total_third_party_commission_amount,
                            $booking->plan->getDurationInMonths(),
                            $booking->membershipRenewal ? $booking->membershipRenewal->due_date : $booking->member->start_date,
                        );

                        $memberPaymentSchedule->timestamps = false;
                        $memberPaymentSchedule->updateQuietly([
                            'third_party_commission_amount' => $programCommissionMonthlyPayment->monthly_charge,
                        ]);

                        $memberPaymentSchedule->payments()->each(function ($payment) use ($memberPaymentSchedule) {
                            $payment->timestamps = false;
                            $payment->updateQuietly([
                                'third_party_commission_amount' => $memberPaymentSchedule->third_party_commission_amount,
                            ]);
                        });

                        $booking->payment->timestamps = false;
                        $booking->payment->updateQuietly([
                            'third_party_commission_amount' => $programCommissionMonthlyPayment->first_charge,
                            'updated_at' => DB::raw('updated_at'),
                        ]);
                    } elseif ($booking->payment) {
                        $booking->payment->timestamps = false;
                        $booking->payment->updateQuietly([
                            'third_party_commission_amount' => $booking->total_third_party_commission_amount,
                            'updated_at' => DB::raw('updated_at'),
                        ]);
                    }
                }
            });

        $this->newLine();

        return Command::SUCCESS;
    }
}
