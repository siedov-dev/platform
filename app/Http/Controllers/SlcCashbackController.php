<?php

namespace App\Http\Controllers;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Device;
use App\Helpers\RdRand;
use App\Models\Slc\ShipBarrelItem;
use App\Models\Slc\SlcBarrelLog;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserInventory;
use App\MUser;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use Illuminate\Http\Request;
use Mockery\Exception;

class SlcCashbackController extends Controller
{
    const BARREL_NEW = 0;
    const BARREL_HALF_BROKEN = 1;
    const BARREL_ALMOST_BROKEN = 2;
    public function shipModal()
    {
        $states = [
            '/img/cashback/anims/Barrel2.json',
            '/img/cashback/anims/Barrel3.json',
            '/img/cashback/anims/Barrel3Open.json',
        ];

        $m_user = MUser::getAuth();
        $inventory = SlcUserInventory::forUser($m_user);

        $gold_balls = $inventory->getItemsCount(SlcItemType::GoldCannonball);
        $black_balls = $inventory->getItemsCount(SlcItemType::Cannonball);

        $user = MUser::getAuth();
        $barrel_state = $user->getValue('barrel_state', 0);

        return $this->viewOrJson(Device::detect() . '.slc.cashback.ship', [
            'gold_balls' => $gold_balls,
            'black_balls' => $black_balls,
            'barrel_state' => $states[$barrel_state],
            'm_user' => $m_user,
            'state' => $barrel_state,
        ]);
    }

    public function shoot(Request $request)
    {
        $animations = [
            '/img/cashback/anims/Barrel1.json',
            '/img/cashback/anims/Barrel2.json',
            '/img/cashback/anims/Barrel3.json',
        ];

        $states = [
            '/img/cashback/anims/Barrel2.json',
            '/img/cashback/anims/Barrel3.json',
            '/img/cashback/anims/Barrel3Open.json',
        ];

        $ball_type = $request->input('ball_type');
        $user = MUser::getAuth();
        $barrel_state = $user->getValue('barrel_state', self::BARREL_NEW);
        $win_text = "";
        $win_image = "";
        $item = null;

        $won = false; //user broke the barrel
        $inventory = $user->getSlcInventory();

        if ($ball_type === 'gold') {// golden ball always cracks the barrel
            if (!$inventory->hasItem(SlcItemType::GoldCannonball)) {
                return [
                    'error' => __('cashback-plus.not_enough_balls')
                ];
            }
            if ($inventory->takeItem(SlcItemType::GoldCannonball) === null) {
                return [
                    'error' => __('cashback-plus.not_enough_balls')
                ];
            }
            $success = true;
        } else {
            if (!$inventory->hasItem(SlcItemType::Cannonball)) {
                return [
                    'error' => __('cashback-plus.not_enough_balls')
                ];
            }
            if ($inventory->takeItem(SlcItemType::Cannonball) === null) {
                return [
                    'error' => __('cashback-plus.not_enough_balls')
                ];
            }
            // 20% chance to crack the barrel
            $success = RdRand::randomInt(0, 4) === 1;
        }

        $animation = $animations[$barrel_state];

        if ($success && $barrel_state === self::BARREL_ALMOST_BROKEN) $won = true;

        if ($won) {
            $items = ShipBarrelItem::all()->shuffle();

            $chance = 0;
            $chances = [];
            //chance range
            foreach ($items as $g_item) {
                $chances[$g_item->getid()]['min'] = $chance;
                $chance += $g_item->getChance();
                $chances[$g_item->getid()]['max'] = $chance;
            }
            $rand_value = RdRand::randomInt(0, 100);

            $win_item_id = null;

            //get item
            foreach ($chances as $id => $ranges) {
                if ($ranges['min'] <= $rand_value && $ranges['max'] >= $rand_value) {
                    $win_item_id = $id;
                    break;
                }
            }

            if ($win_item_id === null) throw new Exception('No item won');

            /** @var ShipBarrelItem $item */
            $item = ShipBarrelItem::findOrFail($win_item_id);

            $user->getSlcInventory()->addItem($item, 'barrel');

            $animation = '/img/cashback/anims/Barrel' . ($barrel_state + 1) . 'Open.json';
            $barrel_state = self::BARREL_NEW;
            $win_text = __('cashback-plus.win', ['prc' => $item->getAmount()]);
            $win_image = "/img/cashback/cashback_plus_piastres/cb_{$item->getAmount()}.svg";
        } elseif ($success) {
            //next barrel state
            $barrel_state = ++$barrel_state % 3;
            $animation = $animations[$barrel_state];
        }

        $user->setValue('barrel_state', $barrel_state);

        $black_balls_left = $inventory->getItemsCount(SlcItemType::Cannonball);
        $gold_balls_left = $inventory->getItemsCount(SlcItemType::GoldCannonball);

        $log = new SlcBarrelLog();
        $log->setUserId($user->getId());
        $log->setBallType($ball_type);
        $log->setSuccess($success);
        $log->setWin($won);
        $log->setPrc($item?->getAmount()?? 0);
        $log->save();

        CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::CannonShot, [
            'ball_type' => $ball_type,
            'success' => $success,
            'broken' => $won,
            'prc' => $item?->getAmount()?? 0,
        ]);

        KSocket::getInstance()->sendEvent($user->getId(), 'inventory:item-changed');

        $resp = [
            'success' => $success,
            'barrel1' => $states[$barrel_state],
            'barrel2' => $animation,
            'won_cashback' => false,
            'cashback' => 0,
            'won' => $won,
            'win_text' => $win_text,
            'win_image' => $win_image,
            'black_balls_left' => $black_balls_left,
            'gold_balls_left' => $gold_balls_left,
            'state' => $barrel_state,
        ];
        return $resp;
    }

}
