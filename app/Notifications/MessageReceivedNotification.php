<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageReceivedNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $sender;

    public function __construct($message, $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
         return [
            'title'          => 'New Message Received',
            'conversation_id' => $this->message->conversation_id,
            'message_id'      => $this->message->id,
            'sender_id'       => $this->sender->id,
            'sender_name'     => $this->sender->first_name . ' ' . $this->sender->last_name,
            'body'            => $this->message->body,
        ];
    }
}
