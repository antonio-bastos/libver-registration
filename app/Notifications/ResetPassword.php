<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Password Reset Request')
            ->line('You have requested a password reset for your account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in '.config('auth.passwords.users.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
