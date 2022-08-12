<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class LogoutEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;
    
    public $token = null;
    
    public function __construct($token)
    {
        $this->token = $token;
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel('logout');
    } 
}
