<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class MangaChapterReleased extends Notification implements ShouldQueue
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

        // If the pivot says YES to email, and the user actually has an email set
        if ($pivot && $pivot->IsEmailNotificationsOn && $notifiable->Email) {
            $channels[] = 'mail';
        }
        
        // If the pivot says YES to telegram, and the user has linked their Telegram
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
            ->subject("New Chapter of {$this->manga->Title} is out!")
            ->greeting("Hello {$notifiable->Username},")
            ->line("A new chapter of {$this->manga->Title} is out!")
            ->line("Read {$this->manga->LastChapterName} right now.")
            ->action('Read Here', $this->manga->SourceLink);
    }

    /**
     * Build the Telegram Message.
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            // Send to the user's saved Telegram ID
            ->to($notifiable->TelegramID)
            ->content("🚨 *New Chapter Alert!*\n\nA new chapter of *{$this->manga->Title}* is out!\nRead *{$this->manga->LastChapterName}* now.")
            ->button('Read Here', $this->manga->SourceLink);
    }
}