<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegistrationStatusUpdated extends Notification
{
    use Queueable;

    protected $event;
    protected $status;

    public function __construct($event, $status)
    {
        $this->event = $event;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your registration for {$this->event->title} is now {$this->status}.",
            'event_id' => $this->event->id,
            'type' => 'status_update'
        ];
    }
}
