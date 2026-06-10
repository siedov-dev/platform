<?php

namespace App\Models\Slc\Activators;

use App\Models\Slc\SlcUserShopItem;

/**
 * Интерфейс активаторов SlcItem.
 * Используется для активации итемов пользователями.
 */
interface SlcItemActivator
{
    /**
     * Активирует итема.
     * @param SlcUserShopItem $item
     * @param array $params
     * @return mixed
     */
    public function activateItem(SlcUserShopItem $item, array $params = []);


    /**
     * Указывает нужно ли отображать дополнительный диалог для активации.
     * @param SlcUserShopItem $item
     * @return bool
     */
    public function requiresDialog(SlcUserShopItem $item): bool;

    /**
     * Возвращает диалог активации если необходимо.
     * @param SlcUserShopItem $item
     * @return string
     */
    public function getDialog(SlcUserShopItem $item): string;
}
