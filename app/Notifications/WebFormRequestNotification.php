<?php

namespace App\Notifications;

use App\Models\BackofficeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class WebFormRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toMail($notifiable)
    {
    }

    public function toTelegram($notifiable)
    {
        $to = $notifiable instanceof BackofficeUser ? '-4026869878' : config(
            'services.telegram-bot-api.booking_chat_id'
        );

        return TelegramMessage::create()
            ->to($to)
            ->content($this->message)
            ->options(['parse_mode' => 'html']);

        // ->view('notification', ['url' => $url])
        // (Optional) Inline Buttons
        //            ->button('View booking', $url)
        //            ->button('Download booking', $url);
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
