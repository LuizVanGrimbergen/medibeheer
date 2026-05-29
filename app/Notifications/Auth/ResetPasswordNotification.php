<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->markdown('mail.auth-notification')
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject(trans('mail.reset_password.subject'))
            ->greeting(trans('mail.reset_password.greeting'))
            ->line(trans('mail.reset_password.line'))
            ->action(trans('mail.reset_password.action'), $url)
            ->line(trans('mail.reset_password.footer'))
            ->salutation(trans('mail.reset_password.salutation')."\n\n".config('mail.brand'));
    }
}
