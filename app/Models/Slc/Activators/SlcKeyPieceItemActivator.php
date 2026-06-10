<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestItem;
use App\Models\Slc\SlcConfig;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcKeyPieceItemActivator implements SlcItemActivator
{

    private function getKeyType(SlcItemType $item): SlcItemType
    {
        return match ($item) {
            SlcItemType::SmallKeyPiece => SlcItemType::KeyLittle,
            SlcItemType::MiddleKeyPiece => SlcItemType::KeyMiddle,
            SlcItemType::BigKeyPiece => SlcItemType::KeyBig,
            SlcItemType::LostKeyPiece => SlcItemType::KeyLost,
            SlcItemType::BermudaKeyPiece => SlcItemType::KeyBermuda,
            SlcItemType::BlackKeyPiece => SlcItemType::KeyBlack,
            default => throw new \Exception("Unknown piece type"),
        };
    }

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {

    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return true;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        $pieces_required = SlcConfig::getParams()->getKeyPiecesRequired($item->getType());
        $user = MUser::findOrFail($item->getUserId());
        $inventory = SlcUserInventory::forUser($user);
        if ($item->getAmount() < $pieces_required) return "not enough shards";

        $key = new SlcUserShopItem();
        $key->setType($this->getKeyType($item->getType()));
        $key->setAmount(1);

        $item->remAmount($pieces_required);
        $inventory->addItem($key, "key pieces");
        $item->save();
        $item->checkStatus();

        return view('common.slc.key-collected-modal', ['key' => $key,]);
    }
}
