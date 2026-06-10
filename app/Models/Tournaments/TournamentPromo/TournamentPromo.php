<?php

namespace App\Models\Tournaments\TournamentPromo;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;
use App\Helpers\UI;
use App\Models\Tournaments\TournamentInterface;
use App\MUser;
use App\Services\Tournaments\TournamentsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Promo tournament model
 * @property int $id
 * @property string $name
 * @property string $fund
 * @property string $min_bet
 * @property int $min_spins
 * @property Carbon $start
 * @property Carbon $end
 * @property string $tile_img
 * @property string $mobile_img
 * @property string $desktop_img
 * @property string $cabinet_d_img
 * @property string $cabinet_m_img
 * @property string $games
 * @property string $conditions
 * @property string $prizes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $test_mode
 */
class TournamentPromo implements TournamentInterface
{
    protected $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];
    private $_data = [];

    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    public function __get(string $name)
    {
        return $this->_data[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->_data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->_data[$name]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->_data[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->_data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->_data[$offset] = null;
    }

    public function toArray(): array
    {
        return $this->_data;
    }

    private $_availableGames;

    public static function getAvailableTournaments(): Collection
    {
        $s = new TournamentsService();
        if (MUser::getAuth()?->isTestUser()) {
            return collect($s->getActivePromoTournaments(true));
//            return self::where('end', '>', now())->get();
        }
        return collect($s->getActivePromoTournaments());
    }

    public static function getCompletedTournaments(): Collection
    {
        $s = new TournamentsService();
        if (MUser::getAuth()?->isTestUser()) {
            return collect($s->getCompletedPromoTournaments(true));
        }
        return collect($s->getCompletedPromoTournaments());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFund(): string
    {
        return $this->fund;
    }

    public function setFund(string $fund): void
    {
        $this->fund = $fund;
    }

    public function getMinBet(): string
    {
        return $this->min_bet;
    }

    public function setMinBet(string $min_bet): void
    {
        $this->min_bet = $min_bet;
    }

    public function getMinSpins(): int
    {
        return $this->min_spins;
    }

    public function setMinSpins(int $min_spins): void
    {
        $this->min_spins = $min_spins;
    }

    public function getStart(): Carbon
    {
        return Carbon::parse($this->start);
    }

    public function setStart(Carbon $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): Carbon
    {
        return Carbon::parse($this->end);
    }

    public function setEnd(Carbon $end): void
    {
        $this->end = $end;
    }

    public function getTileImg(): string
    {
        return $this->tile_img;
    }

    public function setTileImg(string $tile_img): void
    {
        $this->tile_img = $tile_img;
    }

    public function getMobileImg(): string
    {
        return $this->mobile_img;
    }

    public function setMobileImg(string $mobile_img): void
    {
        $this->mobile_img = $mobile_img;
    }

    public function getDesktopImg(): string
    {
        return $this->desktop_img;
    }

    public function setDesktopImg(string $desktop_img): void
    {
        $this->desktop_img = $desktop_img;
    }

    public function getCabinetDImg(): string
    {
        return $this->cabinet_d_img;
    }

    public function setCabinetDImg(string $cabinet_d_img): void
    {
        $this->cabinet_d_img = $cabinet_d_img;
    }

    public function getCabinetMImg(): string
    {
        return $this->cabinet_m_img;
    }

    public function setCabinetMImg(string $cabinet_m_img): void
    {
        $this->cabinet_m_img = $cabinet_m_img;
    }

    public function getGames(): string
    {
        return $this->games;
    }

    public function setGames(string $games): void
    {
        $this->games = $games;
    }

    public function getConditions(): string
    {
        return $this->conditions;
    }

    public function setConditions(string $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function getPrizes(): string
    {
        return $this->prizes;
    }

    public function setPrizes(string $prizes): void
    {
        $this->prizes = $prizes;
    }

    public function isTestMode(): bool
    {
        return $this->test_mode;
    }

    public function setTestMode(bool $test_mode): void
    {
        $this->test_mode = $test_mode;
    }

    public function getImgUrl(): string
    {
        return $this->getTileImg();
    }

    public function getBannerUrl(): string
    {
        return (Device::detect() === Device::DESKTOP ? $this->getDesktopImg(): $this->getMobileImg()) . '?id='.$this->getId();
    }

    public function cabinetBannerUrl(): string
    {
        return (Device::detect() === Device::DESKTOP ? $this->getCabinetDImg(): $this->getCabinetMImg()) . '?id='.$this->getId();
    }

    public function getPrizeFund(): string
    {
        return $this->getFund();
    }

    public function getStartDateTime(): string
    {
        return $this->getStart()->format('Y-m-d H:i:s');
    }

    public function getEndDateTime(): string
    {
        return $this->getEnd()->format('Y-m-d H:i:s');
    }

    public function getAllowedGames(): GamesCollection
    {
        if ($this->_availableGames === null) {

            $allowedGamesData = [];
            if (!empty($this->getGames())) {
                $json = json_decode($this->getGames(), true);
                if (!empty($json) && is_array($json)) {
                    $allowedGamesData = $json;
                }
            } else {
                return new GamesCollection();
            }

            $this->_availableGames = new GamesCollection();

            foreach ($allowedGamesData as $providerId => $gamesIds) {
                $provider = GamesManager::getProvider($providerId);
                if ($provider !== null) {
                    if (!empty($gamesIds)) {
                        foreach ($gamesIds as $gameId) {
                            $game = $provider->getGame('', $gameId);
                            if ($game !== null) $this->_availableGames->addGame($game);
                        }
                    }
                }
            }
        }
        return $this->_availableGames;
    }

    public function isActive(): bool
    {
        return $this->getStart() <= now() && $this->getEnd() > now();
    }

    public function isPendingStart(): bool
    {
        return $this->getStart() > now();
    }

    public function infoUrl(): string
    {
        return route('tournaments.promo-info', ['tournament' => $this->getId()], false);
    }

    public function getStartDate(): string
    {
        return now()->parse($this->getStartDateTime())->format('Y-m-d');
    }

    public function getEndDate(): string
    {
        return now()->parse($this->getEndDateTime())->format('Y-m-d');
    }

    public function renderConditions(?string $locale = null): string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getConditions(), true);
        $description = $texts[$locale]['descr']?? $texts['en']['descr']?? null;
        return UI::textToHtml($description);
    }

    public function renderPrizes(?string $locale = null): string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getPrizes(), true);
        $description = $texts[$locale]['descr']?? $texts['en']['descr']?? null;
        return UI::textToHtml($description);
    }

    public static function findOrFail(int $id): TournamentPromo
    {
        $s = new TournamentsService();
        $p =  $s->getPromoTournament($id);
        if ($p === null) {
            throw new \Exception('Tournament not found');
        }
        return $p;
    }
}
