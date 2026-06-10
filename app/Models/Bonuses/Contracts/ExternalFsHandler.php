<?php

namespace App\Models\Bonuses\Contracts;

use App\GamesProviders\IGameWallet;

interface ExternalFsHandler
{
    public function createFs(string $id): bool;
    public function cancelFs(string $id): void;
    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float;

    public function externFreeSpinWin(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float;

    public function externFreeSpinsCompleted(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float;
    public function updateFsData(): void;
}
