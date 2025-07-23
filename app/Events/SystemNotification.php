<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SystemNotification implements ShouldBroadcast
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $this->data['user_id']);
    }

    public function broadcastAs()
    {
        return 'system.notificacion';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
