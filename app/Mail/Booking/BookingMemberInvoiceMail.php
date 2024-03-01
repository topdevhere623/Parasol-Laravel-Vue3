<?php

/** @noinspection PhpParamsInspection */

namespace App\Mail\Booking;

use App\Models\Booking;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingMemberInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected ?int $booking_id;

    protected string $termsUrl;

    protected string $policyUrl;

    public function __construct(Booking $booking)
    {
        $this->booking_id = $booking->id;

        // Cuz of entertainer subdomain
        $this->termsUrl = route('page.show', 'terms-and-conditions');
        $this->policyUrl = route('page.show', 'privacy-policy');
    }

    public function build()
    {
        $booking = Booking::with('plan.package.program', 'coupon')
            ->findOrFail($this->booking_id);

        return $this->view($this->getView($booking))
            ->subject($this->getSubject($booking))
            ->from($this->getFromEmail($booking), $this->getFrom($booking))
            ->with('booking', $booking)
            ->with('termsUrl', $this->termsUrl)
            ->with('policyUrl', $this->policyUrl)
            ->with('isCheckoutPayment', in_array(optional($booking->paymentMethod)->code, ['monthly', 'checkout']))
            ->with('paymentMethodTitle', $booking->paymentMethod->title);
    }

    protected function getView(Booking $booking): string
    {
        return match (true) {
            $booking->isProgramSource('gems') => 'emails.booking.gems-invoice',
            $booking->isProgramSource('hsbc') => 'emails.booking.hsbc-invoice',
            $booking->plan->package->program->id == Program::ENTERTAINER_SOLEIL_ID => 'emails.booking.entertainer-invoice',
            $booking->plan->package->program->id == Program::RAK_BANK_ID => 'emails.booking.rak-invoice',
            default => 'emails.booking.invoice',
        };
    }

    protected function getSubject(Booking $booking): string
    {
        return match (true) {
            $booking->isProgramSource(
                'hsbc'
            ) => 'Here’s your HSBC ENTERTAINER soleil purchase confirmation',
            $booking->plan->package->program->id == Program::ENTERTAINER_SOLEIL_ID => 'Congratulations on a membership purchase!',
            $booking->plan->package->program->id == Program::RAK_BANK_ID => 'Success! You have completed RAKBANK Elite membership onboarding',
            default => 'Congrats! Your adv+ membership purchase confirmation',
        };
    }

    protected function getFrom(Booking $booking): string
    {
        return match (true) {
            $booking->isProgramSource('hsbc') => 'HSBC ENTERTAINER soleil',
            $booking->plan->package->program->id == Program::ENTERTAINER_SOLEIL_ID => 'ENTERTAINER soleil',
            default => config('mail.from.name'),
        };
    }

    protected function getFromEmail(Booking $booking)
    {
        return match (true) {
            $booking->plan->package->program->id == Program::ENTERTAINER_SOLEIL_ID => 'entertainersoleil@advplus.ae',
            default => 'help@advplus.ae',
        };
    }
}
