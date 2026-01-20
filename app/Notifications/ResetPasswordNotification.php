<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $locale = $notifiable->locale ?? 'nl';
        $appName = $locale === 'nl' ? 'Recollectie' : 'Recollection';

        if ($locale === 'nl') {
            return $this->buildDutchEmail($resetUrl, $appName, $notifiable);
        }

        return $this->buildEnglishEmail($resetUrl, $appName, $notifiable);
    }

    /**
     * Build Dutch email message.
     */
    protected function buildDutchEmail(string $url, string $appName, $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wachtwoord resetten - ' . $appName)
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line('Je ontvangt deze e-mail omdat we een verzoek hebben ontvangen om je wachtwoord te resetten.')
            ->action('Wachtwoord resetten', $url)
            ->line('Deze link is 60 minuten geldig.')
            ->line('Als je geen wachtwoord reset hebt aangevraagd, kun je deze e-mail negeren.')
            ->salutation('Groetjes, ' . $appName);
    }

    /**
     * Build English email message.
     */
    protected function buildEnglishEmail(string $url, string $appName, $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your password - ' . $appName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Regards, ' . $appName);
    }

    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
