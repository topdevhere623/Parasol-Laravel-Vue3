<?php

namespace App\Mail\Booking\Reminder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TESoleilMemberReminderCompleteRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $name, protected string $url, protected string $whatsAppUrl)
    {
    }

    public function build()
    {
        return $this->view(
            'emails.booking.reminder.te-soleil-member-reminder-complete-registration'
        )
            ->subject('One step left to activate your ENTERTAINER SOLEIL')
            ->from('entertainersoleil@advplus.ae')
            ->with('name', $this->name)
            ->with('whatsAppUrl', $this->whatsAppUrl)
            ->with('url', $this->url);
    }
}
