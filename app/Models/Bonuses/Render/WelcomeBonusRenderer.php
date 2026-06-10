<?php

namespace App\Models\Bonuses\Render;

use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\SharedMoneyBonus;
use App\Models\Bonuses\UserBonus;

class WelcomeBonusRenderer implements BonusRenderer
{
    private SharedMoneyBonus $_bonus;

    public function __construct(SharedMoneyBonus $bonus)
    {
        $this->_bonus = $bonus;
    }

    public function renderCabinetBonus(string $device = ''): string
    {
        return view('common.bonuses.welcome-bonus', ['bonus' => $this->_bonus]);
    }

    public function renderPlayButton(string $device = ''): string
    {
        return view('common.bonuses.wb-play-btn', ['bonus' => $this->_bonus]);
    }
}
