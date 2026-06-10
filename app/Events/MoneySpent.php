<?php

namespace App\Events;

use App\GamesProviders\Game;
use App\MUser;
use App\Helpers\Currency;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MoneySpent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var MUser User that spent money */
    public MUser $user;

    /** @var float Amount of spent money */
    public float $amount;

    /** @var string Currency in string code format (RUB, UAH, etc.) */
    public string $currency;

    /** @var string|null Game compound ID */
    public ?string $game_cid = null;

    /** @var string|null Game name */
    public ?string $game_name = null;

    /** @var string|null event source Sport, TvBet, game */
    public ?string $source = null;

    /** @var float total bet made in game session */
    public float $total_bet = 0;
    /** @var float  total win made in game session*/
    public float $total_win = 0;

    /**
     * Create a new event instance.
     *
     * @param MUser $user
     * @param float $amount
     * @param string $currency
     */
    public function __construct(MUser $user, float $amount, string $currency, ?string $source = null, ?Game $game = null, float $total_bet = 0, float $total_win = 0)
    {
        $this->user = $user;
        $this->amount = $amount;

        if (!Currency::isValid($currency)) {
            throw new InvalidArgumentException('Invalid currency format given for '.static::class.' constructor.');
        }

        $this->currency = $currency;
        $this->source = $source;

        if ($game !== null) {
            $this->game_cid = $game->getCompoundId();
            $this->game_name = $game->getName();
        }

        $this->total_bet = $total_bet;
        $this->total_win = $total_win;
    }
}
