<?php

namespace App\Events;

use App\MUser;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AlreadyOnline
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public function __construct(MUser $user)
    {
        $this->_user = $user;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
