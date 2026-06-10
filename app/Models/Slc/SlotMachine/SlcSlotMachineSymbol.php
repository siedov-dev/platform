<?php

namespace App\Models\Slc\SlotMachine;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $drum
 * @property SmSymbolType $type
 */
class SlcSlotMachineSymbol extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => SmSymbolType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDrum(): int
    {
        return $this->drum;
    }

    public function setDrum(int $drum): void
    {
        $this->drum = $drum;
    }

    public function getType(): SmSymbolType
    {
        return $this->type;
    }

    public function setType(SmSymbolType $type): void
    {
        $this->type = $type;
    }

    /**
     * Возвращает символы для барабана.
     * @param int $drum
     * @return SlcSlotMachineSymbol[]
     */
    public static function getForDrum(int $drum)
    {
        return SlcSlotMachineSymbol::where('drum', $drum)
            ->orderBy('id')->get();
    }
}
