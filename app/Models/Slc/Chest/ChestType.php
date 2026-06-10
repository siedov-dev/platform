<?php

namespace App\Models\Slc\Chest;

use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;

enum ChestType: string
{
    case LittleChest = "little_chest";
    case MiddleChest = "middle_chest";
    case BigChest = "big_chest";
    case LostContainer = "lost_container";
    case BlackBox = "black_box";
    case BermudaChest = "bermuda_chest";

//    public static function getSlcItemChestType(SlcItem $item): ChestType
//    {
//        return match ($item->getType()) {
//            SlcItemType::LittleChest => ChestType::LittleChest,
//            SlcItemType::MiddleChest => ChestType::MiddleChest,
//            SlcItemType::BigChest => ChestType::BigChest,
//            SlcItemType::BermudaChest => ChestType::BermudaChest,
//            SlcItemType::LostContainer => ChestType::LostContainer,
//            SlcItemType::BlackBox => ChestType::BlackBox,
//            default => throw new \Exception("Unknown chest type"),
//        };
//    }

    public function getItems()
    {
        return SlcChestItem::where('chest_type', $this->value)
            ->orderBy('group')
            ->get();
    }

    public function getGroupedItems()
    {
        return SlcChestItem::where('chest_type', $this->value)
            ->selectRaw('
                chest_type, `group`, item_type, multiplier, bonus_type, bonus_params
                ,sum(chance) as total_chance
                , min(amount) as amount, max(amount) as max_amount
                , max(max_amount) as max_max_amount
            ')
            ->groupBy('bonus_params')
            ->groupBy('bonus_type')
            ->groupBy('chest_type')
            ->groupBy('group')
            ->groupBy('item_type')
            ->groupBy('multiplier')
            ->get();
    }

    public function getRequiredItem(): SlcItemType
    {
        return match ($this) {
            ChestType::LittleChest => SlcItemType::KeyLittle,
            ChestType::MiddleChest => SlcItemType::KeyMiddle,
            ChestType::BigChest => SlcItemType::KeyBig,
            ChestType::BermudaChest => SlcItemType::KeyBermuda,
            ChestType::LostContainer => SlcItemType::KeyLost,
            ChestType::BlackBox => SlcItemType::KeyBlack,
        };
    }

    public function getProgressItem(): ?array
    {
        if ($this === self::BlackBox) {
            return [
                'item' => SlcItemType::BlackKeyPiece,
                'items_count' => 100,
            ];
        }

        return null;
    }

}
