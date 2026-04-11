<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $resetUrlWeb = config('app.url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->email);
        $mobileDeepLink = 'm5data://reset-password?token=' . $this->token . '&email=' . urlencode($this->email);

        return $this->subject('Reset your ' . config('app.name') . ' password')
            ->view('emails.password_reset')
            ->with([
                'resetUrl' => $resetUrlWeb,
                'mobileDeepLink' => $mobileDeepLink,
            ]);
    }
}
