<?php

namespace App\Models\Tournaments;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;
use App\MUser;
use App\Services\Tournaments\TournamentsService;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Tournament run in k_tournaments service
 */
class GoTournament implements TournamentInterface
{
    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DONE = 2;
    const STATUS_COMPLETED = 3; // completed not awarded
    private array $_data = [];
    private int $_currency = 0;
    private string $_curr_name = "";
    private string $_prize_fund = "";
    private string $_prize_page = "";
    private string $_locale = "";
    private TournamentsService $service;

    public function __construct(array $data, ?int $currency = null, ?string $locale = 'en')
    {
        $this->service = new TournamentsService();
        $this->_data = $data;
        $this->_currency = $currency?: UserWallet::CURRENCY_EUR;
        $this->_curr_name = UserWallet::currencyName($this->_currency);
        $this->_locale = $locale;
        if (!empty($data['model']['prize_fund'])) {
            $pf = json_decode($data['model']['prize_fund'], true);
            $this->_prize_fund = $pf[$locale]['descr']?? '';
        }
        if (!empty($data['model']['prize_page'])) {
            $pp = json_decode($data['model']['prize_page'], true);
            $this->_prize_page = $pp[$locale]['descr']?? '';
        }
    }

    /**
     * Возвращает ид турина в БД
     * @return int
     */
    public function getId(): int
    {
        return $this->_data['model']['id']?? 0;
    }

    /**
     * Возвращает картинку тайла турнира
     * @return string
     */
    public function getImgUrl(): string
    {
        return $this->_data['model']['tile_img']?? '';
    }

    /**
     * Возвращает картинку заголовка описания дл десктопа
     * @return string
     */
    public function getDesktopImg(): string
    {
        return $this->_data['model']['desktop_img']?? '';
    }

    /**
     * Возвращает картинку заголовка описания турнира для мобильных устройств
     * @return string
     */
    public function getMobileImg(): string
    {
        return $this->_data['model']['mobile_img']?? '';
    }

    /**
     * Возвращает картинку турнира для кабинета десктопа
     * @return string
     */
    public function getCabinetDImg(): string
    {
        return $this->_data['model']['cabinet_d_img']?? '';
    }

    /**
     * Возвращает картинку турнира для кабинета мобильных устройств
     * @return string
     */
    public function getCabinetMImg(): string
    {
        return $this->_data['model']['cabinet_m_img']?? '';
    }

    /**
     * Возвращает картинку баннера в зависимости от устройства
     * @return string
     */
    public function getBannerUrl(): string
    {
        return (Device::detect() === Device::DESKTOP ? $this->getDesktopImg(): $this->getMobileImg()) . '?id='.$this->_data['model']['id'];
    }

    /**
     * Возвращает картинку кабинета в зависимости от устройства
     * @return string
     */
    public function cabinetBannerUrl(): string
    {
        return (Device::detect() === Device::DESKTOP ? $this->getCabinetDImg(): $this->getCabinetMImg()) . '?id='.$this->_data['model']['id'];
    }

    /**
     * Возвращает название турнира
     * @return string
     */
    public function getName(): string
    {
        return $this->_data['model']['name']?? '';
    }

    /**
     * Возвращает строку призового фонда турнира
     * @return string
     */
    public function getPrizeFund(): string
    {
        // Если строка установлена вручную
        if (!empty($this->_prize_fund)) return $this->_prize_fund;
        // Иначе вернуть сумму денежных средств призов
        return number_format( $this->_data['prize_bank'][$this->_curr_name]?? 0, 0, '.',  ' ')
            . ' '. UserWallet::currencySymbol($this->_currency);
    }

    /**
     * Возвращает строку даты начала турнира
     * @return string
     */
    public function getStartDateTime(): string
    {
        return Carbon::parse($this->_data['model']['start_time'])->format('Y-m-d H:i:s');
    }

    /**
     * Возвращает строку даты завершения турнира
     * @return string
     */
    public function getEndDateTime(): string
    {
        return Carbon::parse($this->_data['model']['end_time'])->format('Y-m-d H:i:s');
    }

    /**
     * Возвращает строку начала турнира в локализированном формате
     * @return string
     */
    public function getStartTime(): string
    {
        return \Illuminate\Support\Carbon::parse($this->_data['model']['start_time'])->isoFormat("DD.MM.YYYY");
    }

    /**
     * Возвращает строку даты завершения турнира в локализированном формате
     * @return string
     */
    public function getEndTime(): string
    {
        return \Illuminate\Support\Carbon::parse($this->_data['model']['end_time'])->isoFormat("DD.MM.YYYY");
    }

    /**
     * Возвращает коллекцию разрешенных игр турнира
     * @return GamesCollection
     */
    public function getAllowedGames(): GamesCollection
    {
        $games = json_decode($this->_data['model']['game_list'], true);
        $collection = new GamesCollection();
        foreach ($games as $provider => $game_ids) {
            $provider = GamesManager::getProvider($provider);
            foreach ($game_ids as $game_id) {
                $game = $provider?->getGame('', $game_id);
                if ($game !== null) {
                    $collection->addGame($game);
                }
            }
        }
        return $collection;
    }

    /**
     * Возвращает, является ли турнир активным
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->_data['model']['status'] == self::STATUS_ACTIVE);
    }

    /**
     * Возвращает, ожидает ли турнир запуска
     * @return bool
     */
    public function isPendingStart(): bool
    {
        return $this->_data['model']['status'] == self::STATUS_NOT_ACTIVE;
    }

    /**
     * Возвращает урл страницы описания турнира
     * @return string
     */
    public function infoUrl(): string
    {
        return route('tournaments.info', ['tournament' => $this->getId()], false);
    }

    /**
     * Возвращает строку даты начала турнира
     * @return string
     */
    public function getStartDate(): string
    {
        return Carbon::parse($this->_data['model']['start_time']);
    }

    /**
     * Возвращает строку даты завершение турнира
     * @return string
     */
    public function getEndDate(): string
    {
        return Carbon::parse($this->_data['model']['end_time']);
    }

    /**
     * Является ли турнир в тестовом режиме
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->_data['model']['is_test'];
    }

    /**
     * Возвращает строку статуса турнира
     * @return string
     */
    public function getStatusName(): string
    {
        switch ($this->_data['model']['status']) {
            case self::STATUS_NOT_ACTIVE:
                return __('tournament.status.not_active');
            case self::STATUS_ACTIVE:
                return __('tournament.status.active');
            case self::STATUS_DONE:
                return __('tournament.status.done');
            case self::STATUS_COMPLETED:
                return __('tournament.status.done');
        }
        return 'unknown';
    }

    /**
     * Возвращает к-во призовых мест
     * @return int
     */
    public function getPrizePlaces(): int
    {
        return $this->_data['prize_places']?? 0;
    }

    /**
     * Возвращает участвует ли пользователь в турнире
     * @param int $user_id
     * @return bool
     */
    public function userParticipates(int $user_id): bool
    {
        return $this->service->userParticipates($this->getId(), $user_id);
    }

    /**
     * Возвращает ид валюты турнира
     * @return int
     */
    public function getCurrency(): int
    {
        return $this->_currency;
    }

    /**
     * Возвращает минимальную ставку необходимую для начисления очков
     * @return float
     */
    public function getMinBet(): float
    {
        return $this->_data['min_bets'][$this->_curr_name]?? 0;
    }

    /**
     * Возвращается таблицу лидеров турнира
     * @param int $limit лимит таблицы
     * @return array
     */
    public function getWinnersTable(int $limit = 0): array
    {
        return $this->service->getWinners($this->getId(), $limit);
    }

    /**
     * Возвращает позицию пользователя в турнире
     * @param int $user_id
     * @return array
     */
    public function getUserPositon(int $user_id): array
    {
        return $this->service->getUserPosition($this->getId(), $user_id);
    }

    /**
     * Возвращает призы турнира
     * @return array
     */
    public function getPrizes(): array
    {
        return $this->service->getPrizes($this->getId(), $this->_currency);
    }

    /**
     * Возвращает страницу с описанием призов турнира
     * @return string
     */
    public function getPrizePage(): string
    {
        return $this->_prize_page;
    }

    /**
     * Возвращает страницу с описанием турнира
     * @param string $locale
     * @return string
     */
    public function getConditions(string $locale = 'en'): string
    {
        return $this->service->getDescription($this->getId(), $locale);
    }

    /** Устанавливает статус турнира
     * @param int $status
     * @return string
     * @throws \Exception
     */
    public function setStatus(int $status): string
    {
        return $this->service->setTournamentState($this->getId(), $status);
    }

    /**
     * Требует ли турнир принятия участия пользователем
     * @return bool
     */
    public function agreementRequired(): bool
    {
        return $this->_data['model']['aggr_req']?? false;
    }

    /**
     * Возвращает к-во спинов необходимых для получения приза (квалификации)
     * @return int
     */
    public function getSpinsRequired(): int
    {
        return $this->_data['model']['spins_required']?? 0;
    }

}
