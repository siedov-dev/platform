<?php

namespace App\Events;

use App\MUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MoneyDeposit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var MUser */
    public MUser $user;

    /** @var float deposit amount */
    public float $amount;

    /** @var int currency code */
    public int $currency;

    /**
     * Create a new event instance.
     *
     * @param MUser $user
     * @param float $amount
     * @param int $currency
     */
    public function __construct(MUser $user, float $amount, int $currency)
    {
        //
        $this->user = $user;
        $this->amount = $amount;
        $this->currency = $currency;
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
