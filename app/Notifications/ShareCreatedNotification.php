<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Directory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ShareCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouveau répertoire partagé')
            ->greeting("Bonjour {$notifiable->username},")
            ->line("Un répertoire nommé \"{$this->directory->rep_name}\" vous a été partagé.")
            ->action('Voir le répertoire', url('/shared')) // à adapter selon ton frontend
            ->line('Merci d’utiliser notre plateforme !');
    }

    public function toDatabase($notifiable)
    {
        return [
            'directory_id' => $this->directory->id,
            'directory_name' => $this->directory->rep_name,
            'message' => "Le répertoire \"{$this->directory->rep_name}\" vous a été partagé.",
        ];
    }
}
