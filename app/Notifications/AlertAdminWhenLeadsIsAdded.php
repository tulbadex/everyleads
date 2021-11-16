<?php

namespace App\Notifications;

use App\Models\{User, Lead};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertAdminWhenLeadsIsAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $lead;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Lead $lead)
    {
        $this->user = $user;
        $this->lead = $lead;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('New lead has been added by '.$this->user->name)
                    ->action('Login to see', url('/login'))
                    ->line('Lead title is '.$this->lead->title)
                    ->line('You can login to view it!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
