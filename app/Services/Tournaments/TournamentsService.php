<?php

namespace App\Services\Tournaments;

use App\Helpers\Hlog;
use App\Models\Slc\SlcItemType;
use App\Models\Tournaments\GoTournament;
use App\Models\Tournaments\TournamentPromo\TournamentPromo;
use App\MUser;
use App\Services\NatsService\NatsService;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Log;

/**
 * Tournament service API
 * Если в конструктор передан пользователь,
 * то в запросах по умолчанию будут использоваться его данные (валюта, ид, тест)
 */
class TournamentsService
{
    private ?MUser $user = null;
    private string $locale = 'en';

    public function __construct(?MUser $user = null, ?string $locale = null)
    {
        $this->user = $user;
        if ($user !== null) {
            $this->locale = $user->getLocale();
        }
        if ($locale !== null) $this->locale = $locale;
    }

    /**
     * Проверяет доступен ли сервис турниров
     * @return bool
     */
    public function tournamentsEnabled(): bool
    {
        try {
            // если отключен в конфигурации
            if (!config('services.tournamentsEnabled', false)) return false;
            return true;
            return NatsService::getInstance()->getClient()->dispatch("tournaments.ping", null, 0.5)?->body === "pong";
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    /**
     * Возвращает список текущих активных турниров.
     * @param int|null $user_id ид юзера
     * @param int|null $currency ид валюты турнира id (0 - все валюты)
     * @param bool|null $test включать тестовые турниры
     * @return GoTournament[]
     */
    public function getActiveTournaments(?int $user_id = null, ?int $currency = null, ?bool $test = null): array
    {
        if ($user_id === null) $user_id = ($this->user?->getId()?? 0);
        if ($currency === null) $currency = ($this->user?->getCurrency()?? Wallet::CURRENCY_EUR);
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency,
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.active-tournaments", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new GoTournament($data, $currency, $this->locale);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает список текущих турниров ожидающих запуска.
     * @param int|null $user_id ид юзера
     * @param int|null $currency ид валюты турнира/ 0 - все
     * @param bool|null $test включить тестовые турниры
     * @return GoTournament[]
     */
    public function getPendingTournaments(?int $user_id = null, ?int $currency = null, ?bool $test = null): array
    {
        if ($user_id === null) $user_id = $this->user?->getId()?? 0;
        if ($currency === null) $currency = $this->user?->getCurrency()?? Wallet::CURRENCY_EUR;
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency,
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.pending-tournaments", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new GoTournament($data, $currency, $this->locale);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает завершенные турниры
     * @param int|null $user_id ид юзера
     * @param int|null $currency ид валюты турнира/ 0 - все
     * @param bool|null $test включить тестовые турниры
     * @return GoTournament[]
     */
    public function getCompletedTournaments(?int $user_id = null, ?int $currency = null, ?bool $test = null): array
    {
        if ($user_id === null) $user_id = $this->user?->getId()?? 0;
        if ($currency === null) $currency = $this->user?->getCurrency()?? Wallet::CURRENCY_EUR;
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency,
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.completed-tournaments", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new GoTournament($data, $currency, $this->locale);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает доступные турниры (ожидающие и активные)
     * @param int|null $user_id ид юзера
     * @param int|null $currency ид валюты турнира/ 0 - все
     * @param bool|null $test включить тестовые турниры
     * @return GoTournament[]
     */
    public function getAvailableTournaments(?int $user_id = null, ?int $currency = null, ?bool $test = null): array
    {
        $active_tournaments = $this->getActiveTournaments($user_id, $currency, $test);
        $pending_tournaments = $this->getPendingTournaments($user_id, $currency, $test);
        return [...$active_tournaments, ...$pending_tournaments];
    }

    /**
     * Возвращает завершенные турниры по которым не выданы награды
     * @return GoTournament[]
     */
    public function getPendingAwardTournaments(): array
    {
        try {
            $payload = [
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.pending-award", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new GoTournament($data);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает турнир по его ид с данными для определенной валюты
     * @param int $id tournament id
     * @param int|null $currency currency id
     * @return GoTournament|null
     */
    public function getTournament(int $id, ?int $currency = null): ?GoTournament
    {
        if ($currency === null) $currency = $this->user?->getCurrency()?? Wallet::CURRENCY_EUR;

        try {
            $payload = [
                'id' => $id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-tournament", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return null;
                    }
                    $t = new GoTournament($data, $currency, $this->locale);
                    return $t;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    /**
     *Возвращает активный турнир для игры
     * @param string $game_system game system
     * @param string $game_id game id
     * @param int|null $user_id user id
     * @param int|null $currency currency id
     * @param bool $test test tournaments
     * @return GoTournament|null
     */
    public function getGameTournament(string $game_system, string $game_id, ?int $user_id = null, ?int $currency = null, ?bool $test = null): ?GoTournament
    {
        if ($currency === null) $currency = $this->user?->getCurrency()?? Wallet::CURRENCY_EUR;
        if ($user_id === null) $user_id = $this->user?->getId()?? 0;
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'user_id' => $user_id,
                'game_system' => $game_system,
                'game_id' => $game_id,
                'currency' => Wallet::currencyName($currency),
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.game-tournament", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        return null;
                    }
                    if (!isset($data['tournament'])) {
                        Log::error(__CLASS__.__METHOD__." no tournament data in response".var_export($data, true));
                        return null;
                    }
                    return new GoTournament($data['tournament'], $currency);
                } else {
                    Hlog::logException(new \Exception(__CLASS__.__METHOD__." ".var_export($resp, true)));;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    /**
     * Загрузка/перезагрузка турнира
     * @param int $id
     * @return string ok or error message
     */
    public function reloadTournament(int $id): string
    {
        try {
            $payload = [
                'id' => $id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.reload-tournament", $payload)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "";
    }

    /**
     * Перезагрузка всех турниров
     * @return string ok or error message
     */
    public function reloadTournaments(): string
    {
        try {
            $payload = [
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.reload-tournaments", $payload, 10)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "";
    }

    /**
     * Выгрузить турнир
     * @param int $id
     * @return string ok or error message
     */
    public function unloadTournament(int $id): string
    {
        try {
            $payload = [
                'id' => $id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.unload-tournament", $payload)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "";
    }

    /**
     * Устанавливает статус турнира
     * @param int $id tournament id
     * @param int $state tournament status
     * @return string ok or error message
     */
    public function setTournamentState(int $id, int $state): string
    {
        try {
            $payload = [
                'id' => $id,
                'state' => $state,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.set-state", $payload)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "";
    }

    /** Возвращает массив с позицией пользователя в турнире и его турнирными очками
     * @param int $id
     * @param int $limit limit of winners
     * @return array<int, array{user_id: int, points: float, spins_count: int, login: string}> array of position => user_id, points, spins_count, login
     */
    public function getWinners(int $id, int $limit = 0): array
    {
        try {
            $payload = [
                'id' => $id,
                'limit' => $limit,
            ];

            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-values", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return [];
                    }
                    return $data;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает позицию пользователя в турнира
     * и 0 если пользователь не найден в турнире
     * @param int $id tournament id
     * @param int|null $user_id user id
     * @return array{position: int, player: array{user_id: int, points: float, spins_count: int, login: string}}
     */
    public function getUserPosition(int $id, ?int $user_id = null): array
    {
        if ($user_id === null) $user_id = $this->user?->getId()?? 0;

        try {
            $payload = [
                'id' => $id,
                'user_id' => $user_id,
            ];

            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.user-position", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return [];
                    }
                    return $data;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает призы турнира для валюты.
     * По умолчанию используется валюта пользователя либо EUR.
     * @param int $id tournament id
     * @param int|null $currency currency id
     * @return array<int, array{
     *      money_amount: float,
     *      item?: array{
     *          item_type: SlcItemType,
     *          amount: int,
     *          bonus_type: int,
     *          img: string,
     *          item_description: string,
     *          item_eval: float,
     *      },
     *      img: string,
     *      texts: string,
     *  }> array of position => prizes
     */
    public function getPrizes(int $id, ?int $currency = null): array
    {
        if ($currency === null) $currency = $this->user?->getCurrency()?? Wallet::CURRENCY_EUR;
        try {
            $payload = [
                'id' => $id,
                'currency' => UserWallet::currencyName($currency)
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-prizes", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return [];
                    }
                    return $data;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * Возвращает страницы описания и призов турнира для указанной локали
     * @param int $id tournament id
     * @param string $lang локаль
     * @return string HTML code
     */
    public function getDescription(int $id, string $lang): string
    {
        try {
            $payload = [
                'id' => $id,
                'lang' => $lang,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-description", $payload)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return "";
    }

    /**
     * Возвращает принимает ли пользователь участие в турнире
     * @param int $id tournament id
     * @param int $user_id user id
     * @return bool
     */
    public function userParticipates(int $id, int $user_id): bool
    {
        try {
            $payload = [
                'id' => $id,
                'user_id' => $user_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.user-participates", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return false;
                    }
                    return $data['resp'];
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    /**
     * Добавляет пользователя в турнир для турниров требующих подтверждения участия.
     * @param int $id tournament id
     * @param int $user_id user id
     * @return string ok or error message
     */
    public function addParticipant(int $id, int $user_id): string
    {
        try {
            $payload = [
                'id' => $id,
                'user_id' => $user_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.add-participant", $payload)?->body;
            if ($resp !== null) {
                return $resp;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "unknown error";
    }

    public function getActivePromoTournaments(?bool $test = null): array
    {
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.active-promo-tournaments", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new TournamentPromo($data);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getCompletedPromoTournaments(?bool $test = null): array
    {
        if ($test === null) $test = ($this->user?->isTestUser()?? false);

        try {
            $payload = [
                'test' => $test,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.completed-promo-tournaments", $payload)?->body;
            if ($resp !== null) {
                $at = json_decode($resp, true);
                if (is_array($at)) {
                    $t = [];
                    foreach ($at as $data) {
                        $t[] = new TournamentPromo($data);
                    }
                    return $t;
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getPromoTournament(int $id): ?TournamentPromo
    {
        try {
            $payload = [
                'id' => $id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-promo-tournament", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return null;
                    }
                    $t = new TournamentPromo($data);
                    return $t;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getActiveTournamentsGames(): array
    {
        try {
            $payload = [
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-active-tournaments-games", $payload)?->body;
            if ($resp !== null) {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    if (!empty($data['error'])) {
                        Log::error($data['error']);
                        return [];
                    }
                    return $data;
                } else {
                    Log::error(__CLASS__.__METHOD__." ".var_export($data, true));
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getActiveTournamentsGamesCacheDate(): string
    {
        try {
            $payload = [
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("tournaments.get-active-tournaments-games-cache-date", $payload)?->body;
            if ($resp !== null) {
                return json_decode($resp);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return "";
    }

    public function subscribeNotifications(int $tournament_id, int $user_id): bool
    {
        try {
            $payload = [
                'id' => $tournament_id,
                'user_id' => $user_id,
            ];
            return NatsService::getInstance()->getClient()->dispatch("tournaments.add-notify-user", $payload)?->body === 'ok';
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function unsubscribeNotifications(int $tournament_id, int $user_id): bool
    {
        try {
            $payload = [
                'id' => $tournament_id,
                'user_id' => $user_id,
            ];
            return NatsService::getInstance()->getClient()->dispatch("tournaments.rem-notify-user", $payload)?->body === 'ok';
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }
}
