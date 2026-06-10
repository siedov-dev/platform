<?php

namespace App\Models\Tournaments;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use Illuminate\Support\Facades\App;

/**
 * Tournament prize slc item
 * Contains money amount and slc item
 */
class TournamentAwardSlcItem implements SlcItem
{
    private SlcItemType $item_type;
    private string $bonus_params = "";
    private string $bonus_games = "";
    private ?string $img = null;
    private string $texts = "";

    public function __construct(int $place, float $amount = 0, string $currency = "", array $data = [])
    {
        $params['place'] = $place;
        if (!empty($data)) {
            $params['item'] = $data;
        }
        if ($amount > 0) {
            $params['money']['amount'] = $amount;
            $params['money']['currency'] = $currency;
        }
        $this->bonus_params = json_encode($params);
        $this->item_type = SlcItemType::TournamentPrize;
        $this->img = null;
        $this->texts = "";
    }

    public function getId(): int
    {
        return 0;
    }

    public function getItemType(): ?SlcItemType
    {
        return $this->item_type;
    }

    public function setItemType(SlcItemType $item_type): void
    {
        $this->item_type = $item_type;
    }

    public function getAmount(): int
    {
        return 1;
    }

    public function setAmount(int $amount): void
    {

    }

    public function getBonusType(): int
    {
        return 0;
    }

    public function setBonusType(int $bonus_type): void
    {
    }

    public function getBonusParams(): string
    {
        return $this->bonus_params;
    }

    public function setBonusParams(string $bonus_params): void
    {
        $this->bonus_params = $bonus_params;
    }

    public function getBonusGames(): string
    {
        return $this->bonus_games;
    }

    public function setBonusGames(string $bonus_games): void
    {
        $this->bonus_games = $bonus_games;
    }

    public function getType(): SlcItemType
    {
        return $this->getItemType();
    }

    public function setType(SlcItemType $type): void
    {
        $this->item_type = $type;
    }


    public function getParams(): string
    {
        return $this->getBonusParams();
    }

    public function getAllowedGames(): string
    {
        return $this->getBonusGames();
    }

    public function getImage(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    public function getDescription(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTexts(), true);
        return $texts[$locale]['descr']?? $texts['en']['descr']?? null;
    }

    public function getTexts(): ?string
    {
        return $this->texts;
    }

    public function setTexts(string $texts): void
    {
        $this->texts = $texts;
    }

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public function getName(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTexts(), true);
        return $texts[$locale]['name']?? $texts['en']['name']?? null;
    }
}
