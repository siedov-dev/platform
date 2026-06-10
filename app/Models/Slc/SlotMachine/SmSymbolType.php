<?php

namespace App\Models\Slc\SlotMachine;

enum SmSymbolType: string
{
    case Jackpot = "jackpot";
    case Seven = "seven";
    case Crown = "crown";
    case Diamond = "diamond";
    case Clover = "clover";
    case Any = "any";
}
