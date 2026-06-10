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

class EmailVerifiedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MUser $user;
    public string $email;
    public string $email_verification_code;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MUser $user, string $email, string $email_verification_code)
    {
        $this->user = $user;
        $this->email = $email;
        $this->email_verification_code = $email_verification_code;
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
