<?php

namespace App\Models\Bonuses\Contracts;


use App\Helpers\Device;

interface BonusRenderer
{
    public function renderCabinetBonus(string $device = ''): string;
    public function renderPlayButton(string $device = ''): string;
}
