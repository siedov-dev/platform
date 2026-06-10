<?php

namespace App\Models\Tournaments;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use Illuminate\Support\Facades\App;

/**
 * Slc item in tournament award
 */
class TournamentSlcItem implements SlcItem
{
    private SlcItemType $item_type;
    private int $amount;
    private int $bonus_type;
    private string $bonus_params;
    private string $bonus_games;
    private ?string $img;
    private string $texts;

    public function getId(): ?int
    {
        return null;
    }

    public function __construct(array $data)
    {
        $this->item_type = SlcItemType::from($data['item_type']);
        $this->amount = $data['amount'];
        $this->bonus_type = $data['bonus_type']?? 0;
        $this->bonus_params = $data['bonus_params']?? '';
        $this->bonus_games = $data['bonus_games']?? '';
        $this->img = $data['img']?? null;
        $this->texts = $data['texts']?? "";
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
        return $this->amount?? 0;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
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
