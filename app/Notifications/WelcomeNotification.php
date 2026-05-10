<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to VoxChat!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your account has been created successfully.')
            ->line('Start chatting with friends and colleagues right away.')
            ->action('Open VoxChat', url('/chats'))
            ->line('Thank you for joining VoxChat!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'welcome',
            'title'   => 'Welcome to VoxChat!',
            'message' => 'Your account is ready. Start chatting!',
            'url'     => '/chats',
        ];
    }
}
