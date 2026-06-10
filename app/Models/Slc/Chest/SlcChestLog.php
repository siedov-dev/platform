<?php

namespace App\Models\Slc\Chest;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property ChestType $chest_type
 */
class SlcChestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chest_type'
    ];

    protected $casts = [
        'chest_type' => ChestType::class,
    ];

    public static function add(MUser $user, ChestType $type)
    {
        $log = new SlcChestLog();
        $log->user_id = $user->getId();
        $log->chest_type = $type;
        $log->save();
    }

    public static function getOpenChestsCount(MUser $user, ?ChestType $type = null): int
    {
        $qr = SlcChestLog::query();
        $qr->where('user_id', $user->getId());
        if ($type !== null) {
            $qr->where('chest_type', $type);
        }

        return $qr->count();
    }
}
