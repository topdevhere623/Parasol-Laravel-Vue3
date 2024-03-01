<?php

namespace App\Mail\CustomerClubGuide;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CustomerClubGuideMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        //
    }

    public function build(): self
    {
        return $this->view('emails.customer-club-guide.default-customer-club-guide')
            ->subject('Detailed club guide from adv+')
            ->from('memberships@advplus.ae')
            ->with('downloadUrl', 'https://cli.re/kAvJQo')
            ->with(
                'joinUrl',
                URL::website(
                    '?utm_source=guide_request_form&utm_medium=email&utm_campaign=info&utm_content=detailed-club-guide#join'
                )
            );
    }
}
