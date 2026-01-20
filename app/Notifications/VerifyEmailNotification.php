<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $locale = $notifiable->locale ?? 'nl';
        $appName = $locale === 'nl' ? 'Recollectie' : 'Recollection';

        if ($locale === 'nl') {
            return $this->buildDutchEmail($verificationUrl, $appName, $notifiable);
        }

        return $this->buildEnglishEmail($verificationUrl, $appName, $notifiable);
    }

    /**
     * Build Dutch email message.
     */
    protected function buildDutchEmail(string $url, string $appName, $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bevestig je e-mailadres - ' . $appName)
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line('Welkom bij ' . $appName . '! Klik op de onderstaande knop om je e-mailadres te bevestigen.')
            ->action('E-mailadres bevestigen', $url)
            ->line('Deze link is 60 minuten geldig.')
            ->line('Als je geen account hebt aangemaakt, kun je deze e-mail negeren.')
            ->salutation('Groetjes, ' . $appName);
    }

    /**
     * Build English email message.
     */
    protected function buildEnglishEmail(string $url, string $appName, $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your email address - ' . $appName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to ' . $appName . '! Please click the button below to verify your email address.')
            ->action('Verify Email Address', $url)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Regards, ' . $appName);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
