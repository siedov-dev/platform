<?php

namespace App\Models\Slc;

use App\Helpers\Hlog;
use App\MUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

/**
 * Класс для работы с инвентарем пользователя
 */
class SlcUserInventory
{
    private MUser $_user;
    private function __construct(MUser $user)
    {
        $this->_user = $user;
    }

    /**
     * Возвращает экземпляр инвентаря пользователя
     * @param MUser $user
     * @return SlcUserInventory
     */
    public static function forUser(MUser $user): SlcUserInventory
    {
        return new SlcUserInventory($user);
    }

    /**
     * Добавляет итем в инвентарь
     * @param SlcItem $item
     * @param string $source
     * @return SlcUserShopItem|null
     */
    public function addItem(SlcItem $item, string $source = "shop"): ?SlcUserShopItem
    {
        // создаем лог
        $inventory_log = new SlcInventoryLog();

        $inventory_log->setUserId($this->_user->getId());
        $inventory_log->setAmount($item->getAmount());
        $inventory_log->setType($item->getType());
        $inventory_log->setBonusType($item->getBonusType());
        $inventory_log->setParams($item->getParams());
        $inventory_log->setGames($item->getAllowedGames());
        $inventory_log->setSourceId($item->getId());
        $inventory_log->setSource($source);
        $inventory_log->save();

        // если итем slc валюта - просто добавляем в scl кошелек
        if ($item->getType() === SlcItemType::Slc) {
            $this->_user->getSlcWallet()->add($item->getAmount(), $source)->save();
            return null;
        }

        // определяем к-во
        $amount = $item->getAmount();
        if ($amount === 0) $amount = 1;

        // если итем стакабельный добавляем к-во к старому, если его нет создаем новый
        if ($item->getInfo()->isStackable()) {
            /** @var SlcUserShopItem $old_item */
            $old_item = SlcUserShopItem::where('user_id', $this->_user->getId())
                ->where('type', $item->getType()->value)
                ->first();
            if ($old_item !== null) {
                $old_item->setAmount($old_item->getAmount() + $amount);
                $old_item->setStatus(SlcUserItemStatus::New);
                $old_item->save();
                return $old_item;
            }
        }

        // создаем итем в инвентаре
        $user_item = new SlcUserShopItem();
        $user_item->setUserId($this->_user->getId());
        $user_item->setAmount($amount);
        $user_item->setType($item->getType());
        $user_item->setBonusType($item->getBonusType());
        $user_item->setParams($item->getParams());
        $user_item->setAllowedGames($item->getAllowedGames());
        $user_item->setSourceId($item->getId());
        $user_item->setItemSource($source);
        // если не стакабельнрый устанавливаем описание
        if (!$item->getInfo()->isStackable()) {
            $user_item->setImage($item->getImage());
            $user_item->setTexts($item->getTexts());
        }
        // если группировка то устанавливаем  группу
        if ($item->getInfo()->isGroupable()) {
            $user_item->setGroup($item->getType()->value);
        }
        // устанавливаем время жизни
        $user_item->setActiveTo(now()->addDays($item->getInfo()->daysActive()));

        $user_item->save();

        // возвращаем итем инвентаря
        return $user_item;
    }

    /**
     * Возвращает коллекцию всех итемов в инвентаре пользователя
     * @param bool $byGroups группировать итемы groupable
     * @return \Illuminate\Support\Collection|SlcUserShopItem[]
     */
    public function getItems(bool $byGroups = false): Collection
    {
        /** @var SlcUserShopItem[] $items */
        $items = SlcUserShopItem::where('user_id', $this->_user->getId())
            ->where('status', SlcUserItemStatus::New->value)
            ->get();

        $result = [];

        foreach ($items as $item) {
            if ($item->checkStatus() !== SlcUserItemStatus::New) continue;
            if ($byGroups && !empty($item->group_id)) {
                $result[-$item->group_id][] = $item;
            } else {
                $result[] = $item;
            }
        }

        return collect($result);
    }

    /**
     * Возвращает первый доступный итем указанного типа
     * @param SlcItemType $type
     * @return SlcUserShopItem|null
     */
    public function getFirstItem(SlcItemType $type): ?SlcUserShopItem
    {
        return SlcUserShopItem::where('user_id', $this->_user->getId())
            ->where('status', SlcUserItemStatus::New->value)
            ->where('type', $type->value)
            ->first();
    }

    /**
     * Возвращает к-во итемов указанного типа
     * @param SlcItemType $type тип итема
     * @return int количество
     */
    public function getItemsCount(SlcItemType $type): int
    {
        return SlcUserShopItem::where('user_id', $this->_user->getId())
            ->where('status', SlcUserItemStatus::New->value)
            ->where('type', $type->value)
            ->sum('amount');
    }

    /**
     * Возвращает есть ли у пользователя достаточно итемов указанного типа
     * @param SlcItemType $type тип итема
     * @param int $amount к-во
     * @return bool
     */
    public function hasItem(SlcItemType $type, int $amount = 1): bool
    {
        return SlcUserShopItem::where('user_id', $this->_user->getId())
            ->where('status', SlcUserItemStatus::New->value)
            ->where('type', $type->value)
            ->where('amount', '>=', $amount)
            ->first() !== null;
    }

    /**
     * Берет из инвентаря указанное к-во итемов.
     * При этом итемы из инвентаря удяляются/уменшаются.
     * Возвращает полученный итем или null если итема нет/недостаточно.
     * @param SlcItemType $type тип итема
     * @param int $amount к-во
     * @return SlcItem|null
     */
    public function takeItem(SlcItemType $type, int $amount = 1): ?SlcItem
    {
        try {
            DB::beginTransaction();
            /** @var SlcUserShopItem $item */
            $item = SlcUserShopItem::where('user_id', $this->_user->getId())
                ->where('status', SlcUserItemStatus::New->value)
                ->where('type', $type->value)
                ->where('amount', '>=', $amount)
                ->lockForUpdate()
                ->first();

            if ($item === null) throw new Exception('Not enough items');

            $taken_item = $item->replicate();

            $item->remAmount($amount);
            $item->checkStatus();
            $item->save();

            $taken_item->setAmount($amount);
            return $taken_item;
        } catch (\Exception $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }
        return null;
    }

}
