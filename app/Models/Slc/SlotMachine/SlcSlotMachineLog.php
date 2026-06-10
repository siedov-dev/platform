<?php

namespace App\Models\Slc\SlotMachine;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $drum_0
 * @property int $drum_1
 * @property int $drum_2
 * @property SmSymbolType $drum_0_symbol
 * @property SmSymbolType $drum_1_symbol
 * @property SmSymbolType $drum_2_symbol
 * @property integer $reward
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SlcSlotMachineLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'drum_0_symbol' => SmSymbolType::class,
        'drum_1_symbol' => SmSymbolType::class,
        'drum_2_symbol' => SmSymbolType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getDrum0(): int
    {
        return $this->drum_0;
    }

    public function getDrum1(): int
    {
        return $this->drum_1;
    }

    public function getDrum2(): int
    {
        return $this->drum_2;
    }

    public function getDrum0Symbol(): SmSymbolType
    {
        return $this->drum_0_symbol;
    }

    public function getDrum1Symbol(): SmSymbolType
    {
        return $this->drum_1_symbol;
    }

    public function getDrum2Symbol(): SmSymbolType
    {
        return $this->drum_2_symbol;
    }

    public function getReward(): int
    {
        return $this->reward;
    }

    public function setReward(int $reward): void
    {
        $this->reward = $reward;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    public static function getUserTotalSpins(MUser $user): int
    {
        return SlcSlotMachineLog::where('user_id', $user->getId())->count();
    }

}
