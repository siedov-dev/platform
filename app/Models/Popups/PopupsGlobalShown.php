<?php

namespace App\Models\Popups;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $popup_id
 * @property int $user_id
 */
class PopupsGlobalShown extends Model
{
    use HasFactory;

    protected $fillable = ['popup_id', 'user_id'];

    public static function wasPopupShown(int $popup_id, int $user_id): bool
    {
        return PopupsGlobalShown::where('popup_id', $popup_id)->where('user_id', $user_id)->first() !== null;
    }

    public static function setPopupShown(int $popup_id, int $user_id): void
    {
        PopupsGlobalShown::firstOrCreate(['popup_id' => $popup_id, 'user_id' => $user_id]);
    }
}
