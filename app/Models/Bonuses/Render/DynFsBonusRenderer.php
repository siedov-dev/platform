<?php

namespace App\Models\Bonuses\Render;

use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\KUserBonus;

class DynFsBonusRenderer implements BonusRenderer
{
    private KUserBonus $_bonus;

    public function __construct(KUserBonus $bonus)
    {
        $this->_bonus = $bonus;
    }

    public function renderCabinetBonus(string $device = ''): string
    {
        return view('common.bonuses.single-bonus', ['bonus' => $this->_bonus]);
    }

    public function renderPlayButton(string $device = ''): string
    {
        return view('common.bonuses.dynfs-play-btn', ['bonus' => $this->_bonus]);
    }
}
