<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;


    public function __construct(private readonly string $token)
    {}
 
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
 
    public function toMail(object $notifiable): MailMessage
    {
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
    
        return (new MailMessage)
            ->subject('🔐 Reset Your Password')
            ->greeting("Hey {$notifiable->name}, 👋")
            ->line("We received a request to reset the password for your account.")
            ->line("Click the button below to choose a new password:")
            ->action('Reset Password', $this->resetUrl($notifiable))
            ->line("⚠️ This link will expire in **{$expire} minutes**.")
            ->line("If you didn't request a password reset, you can safely ignore this email.")
            ->salutation('Thanks for using our system! 🌟');
    }
    
 
    protected function resetUrl(mixed $notifiable): string
    {
        return Filament::getResetPasswordUrl($this->token, $notifiable);
    }
}
