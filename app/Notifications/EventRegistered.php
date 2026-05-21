<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventRegistered extends Notification
{
    use Queueable;

    protected $event;
    protected $attendee;

    public function __construct($event, $attendee)
    {
        $this->event = $event;
        $this->attendee = $attendee;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "{$this->attendee->name} registered for {$this->event->title}.",
            'event_id' => $this->event->id,
            'type' => 'registration'
        ];
    }
}
