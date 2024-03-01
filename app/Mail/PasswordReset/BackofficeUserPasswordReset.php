<?php

namespace App\Mail\PasswordReset;

use Illuminate\Mail\Mailable;

class BackofficeUserPasswordReset extends Mailable
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): self
    {
        return $this->view('emails.auth.backoffice-users.forgot-password')
            ->subject('Password Reset')
            ->with('data', $this->data);
    }
}
