<?php

namespace App\Http\Controllers\Web\Booking;

use App\Models\Booking;
use Illuminate\View\View;

class BookingCompleteController extends BookingBaseController
{
    public function index(Booking $booking): View
    {
        $booking->load('plan.package.program', 'membershipRenewal');
        $package = $booking->plan->package;

        $this->theme->setFromPackage($package);
        if ($package->program->isProgramSource('hsbc')) {
            $this->theme->showHeaderMenu = false;
            $this->theme->showHeader = true;
        }

        $membershipRenewal = (bool)$booking->membershipRenewal;
        $completeMessage = $membershipRenewal ? $package->renewal_complete_message : $package->complete_message;
        $programName = $package->program->public_name;

        $ui_color = $this->getUiColor($booking);

        $gtagData = [
            'total_price' => $booking->total_price,
            'tax' => $booking->vat_amount,
            'currency' => 'AED',
            'reference_id' => $booking->reference_id,
            'coupon' => optional($booking->coupon)->code,
            'clubs' => $booking->clubs->implode(', '),
            'plan' => $booking->plan->title,
        ];

        return view(
            'layouts.booking.step-4',
            compact('ui_color', 'booking', 'gtagData', 'completeMessage', 'programName', 'membershipRenewal')
        );
    }
}
