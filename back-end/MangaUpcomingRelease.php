<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MangaUpcomingRelease extends Notification implements ShouldQueue
{
    use Queueable;

    public $manga;

    public function __construct($manga)
    {
        $this->manga = $manga;
    }

    /**
     * Determine which channels to send to based on the user's pivot settings.
     */
    public function via($notifiable)
    {
        $channels = [];
        $pivot = $notifiable->pivot;

        if ($pivot && $pivot->IsEmailNotificationsOn && $notifiable->Email) {
            $channels[] = 'mail';
        }
        
        if ($pivot && $pivot->IsTelegramNotificationsOn && $notifiable->TelegramID) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    /**
     * Build the Email Message.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Upcoming Chapter of {$this->manga->Title} has been announced!")
            ->greeting("Hello {$notifiable->Username},")
            ->line("Upcoming Chapter of {$this->manga->Title} has been announced to release on {$this->manga->UpcomingReleaseDate}!")
            #->line("Read {$this->manga->LastChapterName} right now.")
            ->action('Check Here', $this->manga->SourceLink);
    }

    /**
     * Build the Telegram Message.
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->to($notifiable->TelegramID)
            ->content("*Upcoming Chapter Alert!*\n\nAn upcoming chapter of *{$this->manga->Title}* has been announced to release on {$this->manga->UpcomingReleaseDate}!\n")
            ->button('Check Here', $this->manga->SourceLink);
    }
}