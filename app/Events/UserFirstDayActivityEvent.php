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

class UserFirstDayActivityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MUser $user;
    public ?string $prev_active;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MUser $user, ?string $prev_active = null)
    {
        $this->user = $user;
        $this->prev_active = $prev_active;
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
