<?php

namespace App\Models\Slc;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\MUser;

/**
 * Итем геймификации.
 */
interface SlcItem
{
    /**
     * Возвращает id
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Возвращает тип итема
     * @return SlcItemType
     */
    public function getType(): SlcItemType;

    /**
     * Возвращает тип бонуса в итеме для типов, содержащих бонус
     * @return int
     */
    public function getBonusType(): int;

    /**
     * Возвращает к-во елементов.
     * Может использоваться для разных итемов по-разному.
     * Например: для ключей это к-во, а для понижения вейджера процент понижения.
     * @return int
     */
    public function getAmount(): int;

    /**
     * Возвращает json строку параметров итема
     * @return string
     */
    public function getParams(): string;

    /**
     * Возвращает json строку доступных игр бонуса для типов, содержащих бонус
     * @return string
     */
    public function getAllowedGames(): string;

    /**
     * Возвращает url иконки переопределяющей иконку по-умолчанию.
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * Возвращает тект/html с переопределением описания итема.
     * @param string|null $locale
     * @return string|null
     */
    public function getDescription(?string $locale = null): ?string;

    /**
     * Возвращает переопределенное название итема.
     * @param string|null $locale
     * @return string|null
     */
    public function getName(?string $locale = null): ?string;

    /**
     * Возвращает json с текстами переопределения названия и описания.
     * @return string|null
     */
    public function getTexts(): ?string;

    /**
     * Возвращает класс информации о типе итема.
     * @return ShopItemInfo
     */
    public function getInfo(): ShopItemInfo;

    /**
     * Возвращает активатор итема.
     * Используется для взаимодействия с пользователем при активации итема.
     * @return SlcItemActivator
     */
    public function getActivator(): SlcItemActivator;
}
