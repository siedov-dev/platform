<?php

namespace App\Models\Slc\Chest;

use App\Models\Slc\SlcItemType;

enum ChestKeyType: string
{
    case KeyLittle = "key_little";
    case KeyMiddle = "key_middle";
    case KeyBig = "key_big";
    case KeyLost = "key_lost";
    case KeyBlack = "key_black";
    case KeyBermuda = "key_bermuda";

    /**
     * get common key types
     * @return ChestKeyType[]
     */
    public static function getCommon(): array
    {
        return [
            ChestKeyType::KeyLittle,
            ChestKeyType::KeyMiddle,
            ChestKeyType::KeyBig,
        ];
    }

    /**
     * get legendary key types
     * @return ChestKeyType[]
     */
    public static function getLegendary(): array
    {
        return [
            ChestKeyType::KeyBermuda,
            ChestKeyType::KeyLost,
            ChestKeyType::KeyBlack,
        ];
    }

    public function getChestType(): ChestType
    {
        return match ($this) {
            self::KeyLittle => ChestType::LittleChest,
            self::KeyMiddle => ChestType::MiddleChest,
            self::KeyBig => ChestType::BigChest,
            self::KeyLost => ChestType::LostContainer,
            self::KeyBermuda => ChestType::BermudaChest,
            self::KeyBlack => ChestType::BlackBox,
        };
    }

    public function slcItemType(): SlcItemType
    {
        return match ($this) {
            self::KeyLittle => SlcItemType::KeyLittle,
            self::KeyMiddle => SlcItemType::KeyMiddle,
            self::KeyBig => SlcItemType::KeyBig,
            self::KeyLost => SlcItemType::KeyLost,
            self::KeyBermuda => SlcItemType::KeyBermuda,
            self::KeyBlack => SlcItemType::KeyBlack,
        };
    }
}
