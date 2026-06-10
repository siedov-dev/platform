<?php

namespace App\Events;

use App\MUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhoneVerifiedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MUser $user;
    public string $phone;

    /**
     * create new event
     * @param MUser $user
     * @param string $phone
     */
    public function __construct(MUser $user, string $phone)
    {
        $this->user = $user;
        $this->phone = $phone;
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
