<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->from((string) config('mail.from.address'), 'Medibeheer')
            ->subject(trans('mail.verify_email.subject'))
            ->greeting(trans('mail.verify_email.greeting'))
            ->line(trans('mail.verify_email.line'))
            ->action(trans('mail.verify_email.action'), $url)
            ->line(trans('mail.verify_email.footer'));
    }
}
