<?php

namespace App\GamesProviders;

/**
 * @deprecated
 */
interface WalletInCents
{
    public function getBalanceCents(): int;
    public function addCents(int $amount): self;
    public function remCents(int $amount): self;
    public function getDigits(): int;
    public function toFloatValue(?int $value = null): float;
}
