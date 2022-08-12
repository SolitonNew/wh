<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class DeviceChangeEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;
    
    public $data = null;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel('device-changes');
    } 
}
