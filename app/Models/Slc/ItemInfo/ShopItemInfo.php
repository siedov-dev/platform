<?php

namespace App\Models\Slc\ItemInfo;

use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcShopItem;
use App\Models\Slc\SlcUserShopItem;

/**
 * Интерфейс описания тиа SlcItem
 */
interface ShopItemInfo
{
    /**
     * Возвращает название по-умолчанию итема (если название переопределено в SlcItem::getName необходимо использовать его).
     * @param bool $with_amount добавлять к-во элементов к названию
     * @return string
     */
    public function name(bool $with_amount = true): string;

    /**
     * Возвращает короткое описание по-умолчанию итема.
     * @return string
     * @deprecated
     */
    public function getShortDescription(): string;

    /**
     * Возвращает к-во единиц (уменшение wager, проценты, к-во  и т.д.)
     * @return string
     */
    public function amount(): string;

    /**
     * Возвращает единицы измерения итема. (fs, wager, к депозиту...)
     * @return string
     */
    public function units(): string;

    /**
     * Возвращает описание по-умолчанию итема.
     * (если описание переопределено в SlcItem::getDescription необходимо использовать его)
     * @return string
     */
    public function description(): string;

    /**
     * Возвращает тип блока для css некоторых старых итемов.
     * Не желательно к использованию.
     * @return string
     */
    public function blockType(): string;

    /**
     * Рендерит иконку иозображения итема в магазине.
     * @return string
     */
    public function renderShopImage(): string;

    /**
     * Рендерит иконку иозображения итема.
     * @return string
     */
    public function renderItemIcon(): string;

    /**
     * Использовалось для генерации изображение диапазона итемов в сундуках.
     * @param $from
     * @param $to
     * @return string
     */
    public function renderRangeItemIcon($from, $to): string;

    /**
     * Рендерит изображение итема в инвентаре.
     * @param SlcUserShopItem $item
     * @return string
     */
    public function renderInventoryItem(SlcUserShopItem $item): string;

    /**
     * Возвращает SlcItem для которого используется данных класс.
     * @return SlcItem
     */
    public function getItem(): SlcItem;

    /**
     * Возвращает должны ли итемы объединяться (Добавлять к-во вместо создания ново итема).
     * Такие итемы не могут иметь срок действия.
     * @return bool
     */
    public function isStackable(): bool;

    /**
     * Возвращает должны ли итемы данного типа группироваться (отображатся в инвентаре с общим к-вом).
     * При этом они сохраняються как отдельные итемы со своим сроком действия.
     * @return bool
     */
    public function isGroupable(): bool;

    /**
     * Возвращает есть ли срок действия итема.
     * @return bool
     */
    public function isExpirable(): bool;

    /**
     * Возвращает какой срок действия итема должен быть установлен в днях.
     * Используется при добавлении в инвентарь пользователя.
     * @return int
     */
    public function daysActive(): int;

    /**
     * Возвращает можно ли активировать данных итем.
     * @return bool
     */
    public function canActivate(): bool;

    /**
     * Возвращает название действия активации итема (активировать, объединить, получить ...)
     * @return string
     */
    public function getActionName(): string;
}
