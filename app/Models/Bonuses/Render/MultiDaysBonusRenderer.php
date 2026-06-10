<?php

namespace App\Models\Bonuses\Render;

use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\MultiDaysBonus;

class MultiDaysBonusRenderer implements BonusRenderer
{
    private MultiDaysBonus $_bonus;

    public function __construct(MultiDaysBonus $bonus)
    {
        $this->_bonus = $bonus;
    }

    public function renderCabinetBonus(string $device = ''): string
    {
        return view('common.bonuses.multi-days-bonus', ['bonus' => $this->_bonus]);
    }

    public function renderPlayButton(string $device = ''): string
    {
        return view('common.bonuses.multi-days-play-btn', ['bonus' => $this->_bonus])->render();
    }
}
