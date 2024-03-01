<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

class SubjectPrefixSendingMessage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        $prefix = app()->environment('production') ? '' : 'TEST | ';
        $event->message->subject($prefix.$event->message->getSubject());
    }
}
