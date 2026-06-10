<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestItem;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class SlcChestItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return true;
    }

    public function getDialog(SlcUserShopItem $user_item): string
    {
//        $m_user = MUser::getAuth();
//        $chest_type = ChestType::getSlcItemChestType($user_item);
//
//        $won_items = [];
//        $chest_items = SlcChestItem::where('chest_type', $chest_type->value)->get();
//        $groups = $chest_items->max('group');
//
//        //get random prizes
//        foreach (range(1, $groups) as $group) {
//            //random position items in group
//            $group_items = $chest_items->where('group', $group)->shuffle();
//            $chance = 0;
//            $chances = [];
//            //chance range
//            foreach ($group_items as $g_item) {
//                $chances[$g_item->getid()]['min'] = $chance;
//                $chance += $g_item->getChance();
//                $chances[$g_item->getid()]['max'] = $chance;
//            }
//            $rand_value = RdRand::randomInt(0, 10000) / 100;
//
//            //get item in group
//            foreach ($chances as $item => $ranges) {
//                if ($ranges['min'] <= $rand_value && $ranges['max'] >= $rand_value) {
//                    $won_items[] = $item;
//                    break;
//                }
//            }
//        }
//
//        $win_slc_item = SlcChestItem::whereIn('id', $won_items)
//            ->get();
//
//        $user_items = [];
//
//        /** @var SlcChestItem $chest_item */
//        foreach ($win_slc_item as $chest_item) {
//            $new_item = new SlcUserShopItem();
//            $new_item->setType($chest_item->getItemType());
//            $new_item->setBonusType($chest_item->getBonusType());
//            $new_item->setAllowedGames($chest_item->getBonusGames());
//            $new_item->setParams($chest_item->getBonusParams());
//            $new_item->setAmount(random_int($chest_item->getAmountFrom(), $chest_item->getAmountTo()));
//            $new_item->setItemSource($chest_type->name);
//            $new_item->setUserId($m_user->getId());
////            $new_item->save();
//
//            $user_items[] = $new_item;
//        }
//
//        return view('common.slc.chest-dialog', [
//            'chest' => $user_item,
//            'win_items' => $user_items,
//        ]);
    }
}
