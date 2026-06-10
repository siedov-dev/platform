<?php

namespace App\Models\Bonuses\Render;

use App\Models\Bonuses\BonusInterface;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\UserBonus;

class DefaultBonusRenderer implements BonusRenderer
{
    private BonusInterface $_bonus;

    public function __construct(BonusInterface $bonus)
    {
        $this->_bonus = $bonus;
    }

    public function renderCabinetBonus(string $device = ''): string
    {
        return view('common.bonuses.single-bonus', ['bonus' => $this->_bonus]);
    }

    public function renderPlayButton(string $device = ''): string
    {
        return view('common.bonuses.bonus-play-btn', ['bonus' => $this->_bonus]);
    }
}
