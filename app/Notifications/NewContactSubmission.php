<?php

namespace App\Notifications;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactSubmission extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New contact form submission')
            ->line('Name: '.$this->submission->name)
            ->line('Email: '.$this->submission->email)
            ->when($this->submission->organization, fn ($mail) => $mail->line('Organization: '.$this->submission->organization))
            ->when($this->submission->reason, fn ($mail) => $mail->line('Reason: '.$this->submission->reason))
            ->line('Message:')
            ->line(strip_tags($this->submission->message));
    }
}
