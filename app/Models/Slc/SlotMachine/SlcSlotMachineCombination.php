<?php

namespace App\Models\Slc\SlotMachine;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property SmSymbolType $symbol_1
 * @property SmSymbolType $symbol_2
 * @property SmSymbolType $symbol_3
 * @property int $slc_reward
 */
class SlcSlotMachineCombination extends Model
{
    use HasFactory;

    protected $casts = [
        'symbol_1' => SmSymbolType::class,
        'symbol_2' => SmSymbolType::class,
        'symbol_3' => SmSymbolType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getSymbol1(): SmSymbolType
    {
        return $this->symbol_1;
    }

    public function setSymbol1(SmSymbolType $symbol_1): void
    {
        $this->symbol_1 = $symbol_1;
    }

    public function getSymbol2(): SmSymbolType
    {
        return $this->symbol_2;
    }

    public function setSymbol2(SmSymbolType $symbol_2): void
    {
        $this->symbol_2 = $symbol_2;
    }

    public function getSymbol3(): SmSymbolType
    {
        return $this->symbol_3;
    }

    public function setSymbol3(SmSymbolType $symbol_3): void
    {
        $this->symbol_3 = $symbol_3;
    }

    public function getSlcReward(): int
    {
        return $this->slc_reward;
    }

    public function setSlcReward(int $slc_reward): void
    {
        $this->slc_reward = $slc_reward;
    }

    /**
     * Возвращает сумму выигрыша комбинаций барабанов
     * @param SmSymbolType $s1 символ барабана
     * @param SmSymbolType $s2 символ барабана
     * @param SmSymbolType $s3 символ барабана
     * @return int сумма выигрыша, или -1 при джекпоте
     */
    public static function checkCombination(SmSymbolType $s1, SmSymbolType $s2, SmSymbolType $s3): int
    {
        return self::where(function ($q) use($s1) {
                $q->where('symbol_1', $s1);
                $q->orWhere('symbol_1', SmSymbolType::Any);
            })
            ->where(function ($q) use($s2) {
                $q->where('symbol_2', $s2);
                $q->orWhere('symbol_2', SmSymbolType::Any);
            })
            ->where(function ($q) use($s3) {
                $q->where('symbol_3', $s3);
                $q->orWhere('symbol_3', SmSymbolType::Any);
            })
            ->orderBy('slc_reward', 'desc')
            ->first()?->getSlcReward() ?? 0;
    }
}
