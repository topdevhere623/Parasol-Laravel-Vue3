<?php

namespace App\Mail\CustomerClubGuide;

use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EntertainerCustomerClubGuideMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        //
    }

    public function build(): self
    {
        $program = Program::find(Program::ENTERTAINER_SOLEIL_ID);

        if (!$program->getClubDocsLink()) {
            report('Unable to get url for club doc');
        }

        return $this->view('emails.customer-club-guide.entertainer-customer-club-guide')
            ->subject('Detailed club guide from ENTERTAINER SOLEIL')
            ->from('entertainersoleil@advplus.ae')
            ->with('downloadUrl', $program->getClubDocsLink());
    }
}
