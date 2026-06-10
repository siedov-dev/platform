<?php

namespace App\Models\Bonuses\Render;

use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\SharedMoneyBonus;
use App\Models\Bonuses\UserBonus;
use App\Models\Bonuses\WelcomeBonusPack;

class WelcomeBonusPackRenderer implements BonusRenderer
{
    private WelcomeBonusPack $_bonus;

    public function __construct(WelcomeBonusPack $bonus)
    {
        $this->_bonus = $bonus;
    }

    public function renderCabinetBonus(string $device = ''): string
    {
        return view('common.bonuses.welcome-bonus-pack', ['bonus' => $this->_bonus]);
    }

    public function renderPlayButton(string $device = ''): string
    {
        return view('common.bonuses.wb-pack-play-btn', ['bonus' => $this->_bonus]);
    }
}
