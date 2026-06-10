<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Сегменты игр.
 * Используются в crm.
 * @property int $id
 * @property GameConfig[] $games
 * @property string $name
 */
class GameSegment extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(GameConfig::class);
    }

    public static function getGamesFromSegment(array|int $segmentId)
    {
        if(is_array($segmentId)) {
            return GameConfig::query()->whereHas('segments', function ($query) use ($segmentId) {
                $query->whereIn('game_segments.id', $segmentId);
            })->get();
        }

        $gameSegment =  GameSegment::query()->find($segmentId);
        return data_get($gameSegment, 'games');
    }
}
