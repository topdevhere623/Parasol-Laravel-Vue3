<?php

namespace App\Mail\Booking\Reminder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberReminderCompleteRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected string $name;
    protected string $url;

    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    public function build()
    {
        return $this->view(
            'emails.booking.reminder.member-reminder-complete-registration'
        )
            ->subject('Complete your membership registration')
            ->from(config('mail.from.address'))
            ->with('name', $this->name)
            ->with('url', $this->url);
    }
}
