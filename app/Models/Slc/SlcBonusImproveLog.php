<?php

namespace App\Models\Slc;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property SlcItemType $item_type
 * @property int $amount
 * @property int $item_id
 */
class SlcBonusImproveLog extends Model
{
    use HasFactory;

    protected $casts = [
        'item_type' => SlcItemType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getItemType(): SlcItemType
    {
        return $this->item_type;
    }

    public function setItemType(SlcItemType $item_type): void
    {
        $this->item_type = $item_type;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getItemId(): int
    {
        return $this->item_id;
    }

    public function setItemId(int $item_id): void
    {
        $this->item_id = $item_id;
    }

    public static function add(int $user_id, SlcItemType $type, int $amount, int $item_id = 0)
    {
        $log = new SlcBonusImproveLog();
        $log->setUserId($user_id);
        $log->setItemType($type);
        $log->setAmount($amount);
        $log->setItemId($item_id);
        $log->save();
    }

    public static function getUserImprovesCount(MUser $user): int
    {
        return self::where('user_id', $user->getId())->count();
    }
}
