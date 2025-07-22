<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $verificationToken;

    public function __construct(string $verificationToken)
    {
        $this->verificationToken = $verificationToken;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = url("/api/v1/auth/verify-email?token={$this->verificationToken}&email=".urlencode($notifiable->email));

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Thank you for registering with our survey platform.')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not create an account, no further action is required.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_verification',
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'token' => $this->verificationToken,
        ];
    }
}
