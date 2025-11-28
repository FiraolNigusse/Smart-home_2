<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MfaCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected int $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Smart Home MFA Code')
            ->line('Use the code below to finish signing in:')
            ->line("**{$this->code}**")
            ->line('This code expires in 10 minutes.');
    }
}

