<?php

namespace App\Models\Slc\Factories;

use App\Models\Slc\Activators\SlcBezdepItemActivator;
use App\Models\Slc\Activators\SlcBonusItemActivator;
use App\Models\Slc\Activators\SlcCannonballItemActivator;
use App\Models\Slc\Activators\SlcCashbackItemActivator;
use App\Models\Slc\Activators\SlcChestKeyItemActivator;
use App\Models\Slc\Activators\SlcDepositItemActivator;
use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Activators\SlcKeyPieceItemActivator;
use App\Models\Slc\Activators\SlcSlcItemActivator;
use App\Models\Slc\Activators\SlcTournamentPrizeItemActivator;
use App\Models\Slc\Activators\SlcWagerItemActivator;
use App\Models\Slc\Activators\SlcWheelSpinItemActivator;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\ItemInfo\SlcBezdepItemInfo;
use App\Models\Slc\ItemInfo\SlcCannonBallItemInfo;
use App\Models\Slc\ItemInfo\SlcCashbackItemInfo;
use App\Models\Slc\ItemInfo\SlcKeyPieceItemInfo;
use App\Models\Slc\ItemInfo\SlcBonusItemInfo;
use App\Models\Slc\ItemInfo\SlcChestKeyItemInfo;
use App\Models\Slc\ItemInfo\SlcDepositItemInfo;
use App\Models\Slc\ItemInfo\SlcSlcItemInfo;
use App\Models\Slc\ItemInfo\SlcTournamentPrizeItemInfo;
use App\Models\Slc\ItemInfo\SlcUnknownItemInfo;
use App\Models\Slc\ItemInfo\SlcWagerItemInfo;
use App\Models\Slc\ItemInfo\SlcWheelSpinItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use App\MUser;

class SlcItemConcreteFactory implements SlcItemFactory
{
    public function getItemInfo(SlcItem $item, MUser $user = null): ShopItemInfo
    {
        return match ($item->getType()) {
            SlcItemType::DepositPercent => new SlcDepositItemInfo($item),
            SlcItemType::WagerDiminution => new SlcWagerItemInfo($item),
            SlcItemType::Bonus => new SlcBonusItemInfo($item, $user),
            SlcItemType::WheelSpin => new SlcWheelSpinItemInfo($item),
//            SlcItemType::LittleChest,
//            SlcItemType::MiddleChest,
//            SlcItemType::BigChest,
//            SlcItemType::BermudaChest,
//            SlcItemType::LostContainer,
//            SlcItemType::BlackBox => new SlcChestItemInfo($item),
            SlcItemType::KeyLittle,
            SlcItemType::KeyMiddle,
            SlcItemType::KeyBig,
            SlcItemType::KeyLost,
            SlcItemType::KeyBermuda,
            SlcItemType::KeyBlack => new SlcChestKeyItemInfo($item),
            SlcItemType::SmallKeyPiece,
            SlcItemType::MiddleKeyPiece,
            SlcItemType::BigKeyPiece,
            SlcItemType::LostKeyPiece,
            SlcItemType::BermudaKeyPiece,
            SlcItemType::BlackKeyPiece => new SlcKeyPieceItemInfo($item),
            SlcItemType::Slc => new SlcSlcItemInfo($item),
            SlcItemType::Bezdep => new SlcBezdepItemInfo($item),
            SlcItemType::Cannonball,
            SlcItemType::GoldCannonball => new SlcCannonBallItemInfo($item),
            SlcItemType::CashbackPlus => new SlcCashbackItemInfo($item),
            SlcItemType::TournamentPrize => new SlcTournamentPrizeItemInfo($item),
            default => new SlcUnknownItemInfo($item),
        };
    }

    public function getItemActivator(SlcItem $item): SlcItemActivator
    {
        return match ($item->getType()) {
            SlcItemType::DepositPercent => new SlcDepositItemActivator($item),
            SlcItemType::WagerDiminution => new SlcWagerItemActivator($item),
            SlcItemType::Bonus => new SlcBonusItemActivator($item),
            SlcItemType::WheelSpin => new SlcWheelSpinItemActivator($item),
//            SlcItemType::LittleChest,
//            SlcItemType::MiddleChest,
//            SlcItemType::BigChest,
//            SlcItemType::BermudaChest,
//            SlcItemType::LostContainer,
//            SlcItemType::BlackBox => new SlcChestItemActivator($item),
            SlcItemType::KeyLittle,
            SlcItemType::KeyMiddle,
            SlcItemType::KeyBig,
            SlcItemType::KeyLost,
            SlcItemType::KeyBermuda,
            SlcItemType::KeyBlack => new SlcChestKeyItemActivator($item),
            SlcItemType::SmallKeyPiece,
            SlcItemType::MiddleKeyPiece,
            SlcItemType::BigKeyPiece,
            SlcItemType::LostKeyPiece,
            SlcItemType::BermudaKeyPiece,
            SlcItemType::BlackKeyPiece => new SlcKeyPieceItemActivator($item),
            SlcItemType::Slc => new SlcSlcItemActivator($item),
            SlcItemType::Bezdep => new SlcBezdepItemActivator($item),
            SlcItemType::Cannonball,
            SlcItemType::GoldCannonball => new SlcCannonballItemActivator($item),
            SlcItemType::CashbackPlus => new SlcCashbackItemActivator($item),
            SlcItemType::TournamentPrize => new SlcTournamentPrizeItemActivator($item),
            default => throw new \Exception('No item activator'),
        };
    }
}
