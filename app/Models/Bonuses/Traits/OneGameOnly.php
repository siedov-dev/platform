<?php

namespace App\Models\Bonuses\Traits;

use App\Models\Bonuses\UserBonus;

trait OneGameOnly
{
    public function setAllowedGames(?string $games): void
    {
        /** @var UserBonus $this */
        $json = json_decode($games, true);
        if (!empty($json) && is_array($json)) {
            $this->allowed_games = $games;
            $this->games_limited = true;
            $this->game_provider = key($json);
            $this->game_id = current($json[$this->game_provider]);
        } else {
            throw new \Exception('Empty game list');
        }
    }
}
