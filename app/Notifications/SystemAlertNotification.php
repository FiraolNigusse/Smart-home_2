<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $eventType,
        protected string $severity,
        protected ?string $message = null,
        protected array $context = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[Smart Home] {$this->severity} event: {$this->eventType}")
            ->line("A {$this->severity} level system event occurred.")
            ->line("Event: {$this->eventType}");

        if ($this->message) {
            $mail->line($this->message);
        }

        if (! empty($this->context)) {
            $mail->line('Context:')
                ->line(json_encode($this->context, JSON_PRETTY_PRINT));
        }

        return $mail->line('Please review the dashboard for more details.');
    }
}

