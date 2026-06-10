<?php

namespace App\Models\Bonuses\Segments;

use App\Models\BonusAvailable;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusPromocode;
use App\Models\BonusSchedule;
use App\Models\BranchBonusSet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property Collection|UserBonus[] $bonuses
 * @property Collection|BonusPromocode[] $promocodes
 * @property Collection|BonusAvailable[] $bonusesAvailable
 * @property Collection|BonusSchedule[] $bonusesSchedule
 * @property Collection|BranchBonusSet[] $branchBonusSets
 */
class BonusSegment extends Model
{
    use HasFactory;

    const BIRTHDAY = "Birthday";
    const MULTIDAYS = "Многодневный";
    const WB_PACK = "Welcome Bonus Pack";

    public $timestamps = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function promocodes(): BelongsToMany
    {
        return $this->belongsToMany(BonusPromocode::class);
    }

    public function bonuses(): BelongsToMany
    {
        return $this->belongsToMany(UserBonus::class);
    }

    public function bonusesAvailable(): BelongsToMany
    {
        return $this->belongsToMany(BonusAvailable::class, 'bonus_segment_bonus_available');
    }

    public function bonusesSchedule(): BelongsToMany
    {
        return $this->belongsToMany(BonusSchedule::class, 'bonus_segment_bonus_schedule');
    }

    public function branchBonusSets(): BelongsToMany
    {
        return $this->belongsToMany(BranchBonusSet::class, 'branch_bonus_set_bonus_segment', 'bonus_segment_id', 'branch_bonus_set_id');
    }

    public function attachTo(BonusSegmentAttachable $item): void
    {
        $item->bonusSegments()->syncWithoutDetaching([$this->getId()]);
    }

    public static function attachOrCreateByName(BonusSegmentAttachable $attachable, string $segment_name): void
    {
        $segment = BonusSegment::where('name', $segment_name)->first();

        if ($segment === null) {
            $segment = new BonusSegment();
            $segment->name = $segment_name;
            $segment->save();
        }

        $segment->attachTo($attachable);
    }
}
