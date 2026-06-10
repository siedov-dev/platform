<?php

namespace App\Http\Controllers;

use App\CustomerIo\CustomerIo;
use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Helpers\UI;
use App\Models\DescriptionPage;
use App\Models\Popups\UserPopup;
use App\Models\Rank\UserRank;
use App\Models\Rank\UserRankRewarded;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Chest\ChestKeyType;
use App\Models\Slc\Chest\SlcChestItem;
use App\Models\Slc\Chest\SlcChestLog;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcCurrencyExchange;
use App\Models\Slc\SlcExchangeBlockedUser;
use App\Models\Slc\SlcExchangeLog;
use App\Models\Slc\SlcLoginRewardsLog;
use App\Models\Slc\SlcShopItem;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcTaskType;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\Models\Slc\UserTasks\UserDailyTask;
use App\Models\Slc\UserTasks\UserTaskType;
use App\MUser;
use App\PayoutTransaction;
use App\Services\KSlc\KSlc;
use App\Services\KSocket\KSocket;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcController extends Controller
{
    public function tasks(Request $request)
    {
        // если запрос не ajax отображаем загрузчик страниц
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $user = MUser::getAuth();

        // Обновить состояния задач
//        SlcTask::checkAllTasks($user);

        // активный день награды за вход
        $active_day = intval($user->getValue('active_day', 1));
        // дата последней полученной награды за вход
        $login_reward_date = $user->getValue('login_reward_date', null);
        // если прошло более суток от последней награды за вход - сбрасываем активный день
        if ($active_day !== 1 && $login_reward_date !== now()->startOfDay()->subDay()->format('Y-m-d') && $login_reward_date !== now()->format('Y-m-d')) {
            $active_day = 1;
            $user->setValue('active_day', $active_day);
        }

        // получена ли награда за сегодняшний вход
        $login_rewarded = ($login_reward_date === now()->format('Y-m-d'));

        // если награда получена, то отодвигаем активный день назад для правильного отображения,
        // так как активный день буде хранить завтрашний день.
        if ($login_rewarded) {
            $active_day--;
            if ($active_day === 0) $active_day = 7;
        }

//        $daily_tasks = SlcDailyTask::all()->pluck('reward', 'day')->toArray();
        $next_daily = $active_day === 7 ? 1 : $active_day + 1;
        // разрешены ли ежедневные задачи (выполнена задача SlcTaskType::FirstSteps)
        $is_daily_allowed = SlcCompletedTask::getTaskStatus(Auth::id(), SlcTaskType::FirstSteps) === SlcTaskStatus::Rewarded;
        $data = compact('active_day', 'next_daily', 'is_daily_allowed', 'login_reward_date', 'login_rewarded');

        if ($request->ajax()) {
            $this->renderAdaptive('slc.tasks', $data, []);
        } else {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
//            $this->renderAdaptive('slc.tasks', $data, ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function taskRewardModal(Request $request)
    {
        // награда за ежедневный вход
        if (!empty($request->input('login_day'))) {
            $user = MUser::getAuth();

            try {
                // проверка, что не был пропущен день
                $active_day = intval($user?->getValue('active_day', 1));
                $login_reward_date = $user?->getValue('login_reward_date', null);
                if ($active_day !== 1 && $login_reward_date !== now()->startOfDay()->subDay()->format('Y-m-d') && $login_reward_date !== now()->format('Y-m-d')) {
                    $active_day = 1;
                    $user->setValue('active_day', $active_day);
                }

                // проверка не была ли уже получена награда
                $login_rewarded = $login_reward_date === now()->format('Y-m-d');
                if ($login_rewarded) throw new \Exception('Already received');

                // получаем награды дня
                $rewards = \App\Models\Slc\SlcDailyReward::getForDay($active_day);

                // получение наград
                if ($request->isMethod('POST')) {

                    SlcLoginRewardsLog::add($user, $active_day);

                    $user->setValue('login_reward_date', now()->format('Y-m-d'));
                    if ($active_day > 6) {
                        $active_day = 1;
                    } else {
                        $active_day++;
                    }
                    $user->setValue('active_day', $active_day);
                    $inventory = SlcUserInventory::forUser($user);
                    foreach ($rewards as $reward) {
                        $inventory->addItem($reward, 'login-reward-'.$active_day);
                    }
                    return redirect()->route('slc.tasks');
                }

                // отображаем модалку для получения наград
                $this->renderAdaptive('slc.login-reward-modal', compact('active_day', 'rewards'), []);
                return $this->sendPreparedResponse();

            } catch (\Exception $e) {
                Hlog::logException($e);
                return "Error";
            }
        }

        //daily task reward
        if (!empty($request->input('daily_task_id'))) {
            $user = MUser::getAuth();

            try {
//                DB::beginTransaction();
                /** @var UserDailyTask $daily_task */
//                $daily_task = UserDailyTask::where('id', $request->input('daily_task_id'))->lockForUpdate()->first();
                $daily_task = KSlc::getInstance()->getUserDailyTask($user->getId(), $request->input('daily_task_id'));
                if ($daily_task === null) throw new \Exception("Task not found");
                if ($daily_task->isRewarded()) throw new \Exception("Already rewarded");
                $rewards = $daily_task->getRewards();

                if ($request->isMethod('POST')) {
                    $daily_task->setRewarded();
                    if (!$daily_task->isRewarded()) throw new \Exception("Can't set rewarded task, maybe already rewarded");
                    $inventory = SlcUserInventory::forUser($user);
                    foreach ($rewards as $reward) {
                        $inventory->addItem($reward, 'daily-task-'.$daily_task->getId());
                    }
                    return redirect()->route('slc.tasks');
                }

                $this->renderAdaptive('slc.daily-reward-modal', compact('daily_task', 'rewards'), []);
                return $this->sendPreparedResponse();

            } catch (\Throwable $e) {
                Hlog::logException($e);
                return "Error";
            } finally {
//                DB::commit();
            }
        }

        // стартовые задачи
        $completed_task_id = $request->input('task_id');
        try {
            $user = MUser::getAuth();
            $completed_task = SlcCompletedTask::getTask($user->getId(), SlcTaskType::from($completed_task_id));
            if ($completed_task === null) throw new \Exception("Task not found");
            if ($completed_task->getStatus() !== SlcTaskStatus::Completed) throw new \Exception("Task not completed");
//            DB::beginTransaction();
//            /** @var SlcCompletedTask $completed_task */
//            $completed_task = SlcCompletedTask::where('id', $completed_task_id)
//                ->where('status', SlcTaskStatus::Completed->value)
//                ->lockForUpdate()
//                ->first();

            if ($completed_task === null) {
                throw new \Exception("Task not found");
            }

            $task = SlcTask::getByType($completed_task->getTaskType());

            if ($request->isMethod('POST')) {
                $completed_task->setStatus(SlcTaskStatus::Rewarded);
//                $completed_task->save();
                $inventory = SlcUserInventory::forUser(MUser::getAuth());
                $inventory->addItem($task->getType()->getTaskChecker()->getReward($task), 'start-tasks');
//                DB::commit();

                // если завершены все стартовые задачи, выдать попап
                if ($task->getType() === \App\Models\Slc\SlcTaskType::FirstSteps) {
                    UserPopup::showCompletedTasks(Auth::id());
                }

                return redirect()->route('slc.tasks');
            }

            if ($request->ajax()) {
                $this->renderAdaptive('slc.task-reward-modal', compact('task', 'completed_task'), []);
            } else {
                $this->renderAdaptive('slc.task-reward-modal', compact('task', 'completed_task'), ['layouts.main']);
            }
        } catch (\Exception $e) {
//            DB::rollBack();
            Log::error($e->getMessage());
        }
//        DB::commit();
        return $this->sendPreparedResponse();
    }

    public function shop(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $user = MUser::getAuth();
        $is_exchange_blocked = SlcExchangeBlockedUser::isUserBlocked($user->getId());
        $user?->setValue('last_shop_visited', now()->format('Y-m-d H:i:s'));
        $show_exchange = $request->route()->getName() === 'slc.exchange';

        $items = SlcShopItem::where('disabled', false)->orderBy('priority', 'asc')->get();
        $exchange_items = SlcCurrencyExchange::where('currency', $user->getCurrencyName())->get();

        if ($request->ajax()) {
            $this->renderAdaptive('slc.shop', compact('items', 'exchange_items', 'show_exchange', 'is_exchange_blocked'), []);
        } else {
            $this->renderAdaptive('slc.shop', compact('items', 'exchange_items', 'show_exchange', 'is_exchange_blocked'), ['layouts.main']);
        }

        return $this->sendPreparedResponse();
    }

    public function exchange(Request $request)
    {
        $EXCHANGE_WAGER = 3;

        $m_user = MUser::getAuth();

        // проверка возможности обмена по рангу
        if ($m_user->getRank()->getLevel() < 4) return $this->viewOrJson('common.rank-required-modal', [
            'title' => __('ranks.shop_required'),
            'rank_level' => 4,
            'rank' => UserRank::getForLvl(4, $m_user->getCurrency()),
            'user' => $m_user,
            'currency' => $m_user->getCurrency(),
        ]);

        // получает итем обмена
        /** @var SlcCurrencyExchange $item */
        $item = SlcCurrencyExchange::findOrFail($request->input('item_id'));

        // обмен
        if ($request->isMethod('POST')) {
            if (SlcExchangeBlockedUser::isUserBlocked($m_user->getId())) {
                $user = MUser::getAuth();
                if ($user === null) return redirect()->route('login');
                try {
                    DB::beginTransaction();
                    if ($item->getSlcAmount() <= $user->getSlcBalance()) {
                        $wallet = $user->getWallet(UserWallet::getCurrencyCode($item->getCurrency()));
                        $user->getSlcWallet()->rem($item->getSlcAmount(), 'exchange item#' . $item->getId());
                        $wallet->addMoney($item->getCurrencyAmount());
                        // вейджер на основные
                        $wallet->addLockedMoney($item->getCurrencyAmount() * $EXCHANGE_WAGER);

                        DB::table('payout_transactions')
                            ->insert([
                                'user_id' => $user->id,
                                'hall_id' => $user->hall_id,
                                'date' => date('Y-m-d H:i:s'),
                                'sum' => $item->getCurrencyAmount(),
                                'status_id' => PayoutTransaction::STATUS_SLC_EXCHANGE,
                                'currency' => $item->getCurrency(),
                                'operation' => 'Slc exchange'
                            ]);

                        SlcExchangeLog::log($user->getId(), $item->getCurrency(), $item->getSlcAmount(), $item->getCurrencyAmount());
                    }
                    DB::commit();


                } catch (\Exception $e) {
                    DB::rollBack();
                    Hlog::logException($e);
                } finally {
                    CustomerIo::syncData($user->getId());
                }
            }

            return redirect()->route('slc.exchange');
        }

        $this->renderAdaptive('slc.exchange-item', ['item' => $item], []);
        return $this->sendPreparedResponse();
    }

    public function buyItemModal(Request $request)
    {
        $m_user = MUser::getAuth();

        if ($m_user->getRank()->getLevel() < 3) return $this->viewOrJson('common.rank-required-modal', [
                'title' => __('ranks.shop_required'),
                'rank_level' => 3,
                'rank' => UserRank::getForLvl(3, $m_user->getCurrency()),
                'user' => $m_user,
                'currency' => $m_user->getCurrency(),
            ]);

        /** @var SlcShopItem $item */
        $item = SlcShopItem::findOrFail($request->input('item_id'));
        $this->renderAdaptive('slc.buy-item', ['item' => $item], []);
        return $this->sendPreparedResponse();
    }

    public function buyItem(Request $request)
    {
        $user = MUser::getAuth();
        if ($user === null) return redirect()->route('login');
        try {
            DB::beginTransaction();
            $buy_amount = $request->input('amount', 1);
            /** @var SlcShopItem $item */
            $item = SlcShopItem::findOrFail($request->input('item_id'));
            $inventory = SlcUserInventory::forUser($user);
            if ($buy_amount < 1) $buy_amount = 1;
            $item_amount = $item->getAmount();
            if ($item_amount === 0) $item_amount = 1;
            $item->setAmount($item_amount * $buy_amount);
            if ($item->getPrice() * $buy_amount <= $user->getSlcBalance()) {
                $user->getSlcWallet()->rem($item->getPrice() * $buy_amount, $item->getInfo()->name() . " #" . $item->getId())->save();
                $inventory->addItem($item, "shop");
            }
            DB::commit();
            KSlc::getInstance()->dailyTaskAddCurrentValue($user->getId(), UserTaskType::ButItem, "", 1);

        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        } finally {
            CustomerIo::syncData($user->getId());
        }

        return redirect()->route('slc.user-items');
        return self::userItems($request);
    }

    public function userItems(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $user = MUser::getAuth();
        $user?->setValue('last_inventory_visited', now()->format('Y-m-d H:i:s'));

        $my_items = SlcUserInventory::forUser($user)->getItems(true);

        if ($request->ajax()) {
            $this->renderAdaptive('slc.user-items', compact('my_items'), []);
        } else {
            $this->renderAdaptive('slc.user-items', compact('my_items'), ['layouts.main']);
        }

        return $this->sendPreparedResponse();
    }

    public function itemInfo(Request $request)
    {
        /** @var SlcShopItem $item */
        $item = SlcShopItem::findOrFail($request->input('item_id'));
        if (!empty($item->getDescription())) return $this->viewOrJson(Device::detect().'.slc.item-info.item-custom-info', ['info' => UI::textToHtml($item->getDescription())]);
        return $item->getInfo()->description();
    }

    public function userItemInfo(Request $request)
    {
        /** @var SlcUserShopItem $item */
        $item = SlcUserShopItem::findOrFail($request->input('item_id'));
        if (!empty($item->getDescription())) return $this->viewOrJson(Device::detect().'.slc.item-info.item-custom-info', ['info' => UI::textToHtml($item->getDescription())]);
        return $item->getInfo()->description();
    }

    public function activateItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = MUser::getAuth();
            /** @var SlcUserShopItem $item */
            $item = SlcUserShopItem::where('id', $request->input('item_id'))
                ->where('user_id', $user->getId())
                ->where('status', SlcUserItemStatus::New)
                ->lockForUpdate()
                ->first();
            $item?->activate($request->all());
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }

        return back();
    }

    public function activateModal(Request $request)
    {
        $user = MUser::getAuth();
        /** @var SlcUserShopItem $item */
        $item = SlcUserShopItem::where('id', $request->input('item_id'))
            ->where('user_id', $user->getId())
            ->where('status', SlcUserItemStatus::New)
            ->first();

        return $item->getActivator()->getDialog($item);
    }

    public function achievements(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $achievements = SlcAchievement::where(function ($q) {
                $q->whereNull('currency');
                $q->orWhere('currency', '');
                $q->orWhere('currency', MUser::getAuth()->currency);
            })
            ->where('is_disabled', false)
            ->orderBy('priority', 'asc')
            ->get();

        $user = MUser::getAuth();
        $ranks = UserRank::getAllRanks($user->getCurrency());
        $current_level = $user->getRank()->getLevel();
        $rank_rewards = $current_level - UserRankRewarded::takenRewardsCount($user);
        $show_ranks = $request->route()->getName() === 'slc.ranks';

        if ($request->ajax()) {
            $this->renderAdaptive('slc.achievements.achievements', [
                'achievements' => $achievements,
                'ranks' => $ranks,
                'user' => $user,
                'current_level' => $current_level,
                'rank_rewards' => $rank_rewards,
                'show_ranks' => $show_ranks,
            ], []);
        } else {
            $this->renderAdaptive('slc.achievements.achievements', [
                'achievements' => $achievements,
                'ranks' => $ranks,
                'user' => $user,
                'current_level' => $current_level,
                'rank_rewards' => $rank_rewards,
                'show_ranks' => $show_ranks,
            ], ['layouts.main']);
        }

        return $this->sendPreparedResponse();
    }

    public function achievementPopup(Request $request)
    {
        /** @var SlcAchievement $achievement */
        $achievement = SlcAchievement::findOrFail($request->input('achievement_id'));

        $this->renderAdaptive('slc.achievements.popup', [
            'achievement' => $achievement,
        ], []);

        return $this->sendPreparedResponse();
    }

    public function achievementReward(Request $request)
    {
        try {
            DB::beginTransaction();
            $m_user = MUser::getAuth();
            /** @var SlcAchievement $achievement */
            $achievement = SlcAchievement::where('id', $request->input('id'))->lockForUpdate()->first();
            if ($achievement->checkCompleted($m_user) && !$achievement->getUserAchievement($m_user->getId())->isRewarded()) {
                $user_ach = $achievement->getUserAchievement($m_user->getId());
                //mark as rewarded
                $user_ach->rewarded = true;
                $user_ach->save();

                //give rewards
                $inventory = SlcUserInventory::forUser($m_user);
                foreach ($achievement->getRewards() as $reward) $inventory->addItem($reward, 'achievement #'.$achievement->getId());
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return back();
    }

    public function treasures(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $inventory = SlcUserInventory::forUser(MUser::getAuth());
        $this->renderAdaptive('slc.treasures.treasures', [
            'inventory' => $inventory,
        ], []);

        return $this->sendPreparedResponse();
    }

    /**
     * Обновление вкладок сундуков
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function treasureTab(Request $request)
    {
        $inventory = SlcUserInventory::forUser(MUser::getAuth());
        $key_types = $request->input('key_types') === 'common'? ChestKeyType::getCommon() : ChestKeyType::getLegendary();
        $this->renderAdaptive('slc.treasures.treasure-tab', [
            'inventory' => $inventory,
            'key_types' => $key_types,
        ], []);

        return $this->sendPreparedResponse();
    }

    public function openChest(Request $request)
    {
        $m_user = MUser::getAuth();
        $key_type = ChestKeyType::from($request->input('key'));
        $chest_type = $key_type->getChestType();

        $inventory = SlcUserInventory::forUser($m_user);

        $user_keys = $inventory->getFirstItem($key_type->slcItemType());

        // check if there`s enough keys
        if ($user_keys === null || $user_keys->getAmount() <= 0) {
            $item = new SlcUserShopItem();
            $item->setType($key_type->slcItemType());
            $item->setAmount(0);
            return $this->viewOrJson('common.slc.no-keys-dialog', ['item' => $item]);
        }

        // take one key
        $take_key = $inventory->takeItem($key_type->slcItemType());
        if ($take_key === null) {
            $item = new SlcUserShopItem();
            $item->setType($key_type->slcItemType());
            $item->setAmount(0);
            return $this->viewOrJson('common.slc.no-keys-dialog', ['item' => $item]);
        }

        $won_items = [];
        $chest_items = SlcChestItem::where('chest_type', $chest_type->value)->get();
        $groups = $chest_items->max('group');

        //get random prizes
        foreach (range(1, $groups) as $group) {
            //random position items in group
            $group_items = $chest_items->where('group', $group)->shuffle();
            $chance = 0;
            $chances = [];
            //chance range
            foreach ($group_items as $g_item) {
                $chances[$g_item->getid()]['min'] = $chance;
                $chance += $g_item->getChance();
                $chances[$g_item->getid()]['max'] = $chance;
            }
            $rand_value = RdRand::randomInt(0, 10000) / 100;

            //get item in group
            foreach ($chances as $item => $ranges) {
                if ($ranges['min'] <= $rand_value && $ranges['max'] >= $rand_value) {
                    $won_items[] = $item;
                    break;
                }
            }
        }

        $win_slc_item = SlcChestItem::whereIn('id', $won_items)
            ->get();

        $user_items = [];

        /** @var SlcChestItem $chest_item */
        foreach ($win_slc_item as $chest_item) {
            $new_item = new SlcUserShopItem();
            $new_item->setType($chest_item->getItemType());
            $new_item->setBonusType($chest_item->getBonusType());
            $new_item->setAllowedGames($chest_item->getBonusGames());
            $new_item->setParams($chest_item->getBonusParams());
            $new_item->setAmount($chest_item->getAmount());
            $new_item->setItemSource($chest_type->name);
            $new_item->setUserId($m_user->getId());
            $new_item->setActiveTo(now()->addDays(3));

            if ($chest_item->getMaxAmount() > $chest_item->getAmount()) {
                $new_item->setAmount(RdRand::randomInt($chest_item->getAmount(), $chest_item->getMaxAmount()));
            }

            if ($chest_item->getMultiplier() > 1) {
                $new_item->setAmount($new_item->getAmount() * $chest_item->getMultiplier());
            }

            $inventory->addItem($new_item, 'chest '.$chest_type->name);

            $user_items[] = $new_item;
        }

        SlcChestLog::add($m_user, $chest_type);

        if (in_array($key_type, ChestKeyType::getCommon())) {
            KSocket::getInstance()->sendEvent($m_user->getId(), "chest:open-common");
        } else {
            KSocket::getInstance()->sendEvent($m_user->getId(), "chest:open-legendary");
        }

        return $this->viewOrJson(Device::detect().'.slc.treasures.chest-dialog', [
            'chest_type' => $chest_type,
            'win_items' => $user_items,
            'keys' => $user_keys->getAmount(),
            'key_type' => $key_type,
        ]);
    }

    // todo: move to another controller
    public function pageInfo(Request $request)
    {
        /** @var DescriptionPage $page */
        $page = DescriptionPage::where('name', $request->input('page'))->first();
        if ($page !== null) return $this->viewOrJson('common.page-info.custom', ['description' => $page->getPage()]);

        return match ($request->input('page')) {
            'shop' => view('common.page-info.shop'),
            'my_tasks' => view('common.page-info.my_tasks'),
            'my_items' => view('common.page-info.my_items'),
            'achievements' => view('common.page-info.achievements'),
            'lost_treasures' => view('common.page-info.lost_treasures'),
            'slc' => view('common.page-info.slc'),
            'wheel' => view('common.page-info.wheel'),
            'cashback-plus' => view('common.page-info.cashback-plus'),
            'referral' => view('common.page-info.referral'),
            default => throw new \Exception("unknown page ".$request->input('page')),
        };
    }
}
