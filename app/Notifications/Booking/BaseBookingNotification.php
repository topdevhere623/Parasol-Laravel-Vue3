<?php

namespace App\Notifications\Booking;

use App\Enum\Booking\StepEnum;
use App\Models\BackofficeUser;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class BaseBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // @TODO: после перехода на php8.2, использовать enum->value
    public const STEP_TITLES = [
        'default' => '1 (Your package)',
        'payment' => '2 (Payment)',
        'membership_details' => '3 (Member details)',
    ];

    protected StepEnum $step;

    protected $bookingId;

    protected ?Booking $booking;

    public function __construct($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function via($notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        $to = $notifiable instanceof BackofficeUser ? '-4026869878' : config(
            'services.telegram-bot-api.booking_chat_id'
        );
        return TelegramMessage::create()
            ->to($to)
            ->content(implode(PHP_EOL, $this->telegramData()))
            ->options(['parse_mode' => 'html']);
    }

    public function telegramData(): array
    {
        $booking = $this->getBooking();
        $renewal = $booking->is_renewal ? ' (Renewal)' : '';
        $lines = [
            '<b>Booking'.$renewal.': '.($booking->plan->package->program->name).'</b>',
            'Booking ID: <a href="'.\URL::backoffice(
                'bookings/'.$booking->id.'/view'
            ).'">'.$booking->reference_id.'</a>',
            'Completed step: '.static::STEP_TITLES[$this->step->value],
            'Package: '.$booking->plan->title,
            'Name: '.$booking->name,
            'Email: '.$booking->email,
        ];
        $lines[] = 'Total price: <b>AED '.money_formatter($booking->total_price).'</b>';
        return $lines;
    }

    protected function getBooking(): ?Booking
    {
        $this->booking ??= Booking::with('plan.package.program', 'coupon.couponable')->findOrFail(
            $this->bookingId
        );
        return $this->booking;
    }
}
