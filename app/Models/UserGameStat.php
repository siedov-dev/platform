<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Игровая сессия пользователей.
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $session_id
 * @property string $system
 * @property string $group
 * @property string $game_id
 * @property float $login_balance
 * @property float $logout_balance
 * @property float $balance_diff
 * @property float $full_total_bet
 * @property float $full_total_win
 * @property float $real_total_bet
 * @property float $real_total_win
 * @property float $total_bet
 * @property float $total_win
 * @property Carbon $login_date
 * @property Carbon $logout_date
 * @property Carbon $last_active_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $outer_table
 * @property int $outer_id
 * @property int $status
 * @property int $bonus_id
 */
class UserGameStat extends Model
{
    use HasFactory;

    const STATUS_OK = 0;
    const STATUS_MODIFIED = 1;

    protected $dates = [
        'login_date',
        'logout_date',
        'last_active_date',
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getSessionId(): string
    {
        return $this->session_id;
    }

    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    public function getSystem(): string
    {
        return $this->system;
    }

    public function setSystem(string $system): void
    {
        $this->system = $system;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getGameId(): string
    {
        return $this->game_id;
    }

    public function setGameId(string $game_id): void
    {
        $this->game_id = $game_id;
    }

    public function getLoginBalance(): float
    {
        return $this->login_balance;
    }

    public function setLoginBalance(float $login_balance): void
    {
        $this->login_balance = $login_balance;
    }

    public function getLogoutBalance(): float
    {
        return $this->logout_balance;
    }

    public function setLogoutBalance(float $logout_balance): void
    {
        $this->logout_balance = $logout_balance;
    }

    public function getBalanceDiff(): float
    {
        return $this->balance_diff;
    }

    public function setBalanceDiff(float $balance_diff): void
    {
        $this->balance_diff = $balance_diff;
    }

    public function getFullTotalBet(): float
    {
        return $this->full_total_bet;
    }

    public function setFullTotalBet(float $full_total_bet): void
    {
        $this->full_total_bet = $full_total_bet;
    }

    public function getFullTotalWin(): float
    {
        return $this->full_total_win;
    }

    public function setFullTotalWin(float $full_total_win): void
    {
        $this->full_total_win = $full_total_win;
    }

    public function getRealTotalBet(): float
    {
        return $this->real_total_bet;
    }

    public function setRealTotalBet(float $real_total_bet): void
    {
        $this->real_total_bet = $real_total_bet;
    }

    public function getRealTotalWin(): float
    {
        return $this->real_total_win;
    }

    public function setRealTotalWin(float $real_total_win): void
    {
        $this->real_total_win = $real_total_win;
    }

    public function getTotalBet(): float
    {
        return $this->total_bet;
    }

    public function setTotalBet(float $total_bet): void
    {
        $this->total_bet = $total_bet;
    }

    public function getTotalWin(): float
    {
        return $this->total_win;
    }

    public function setTotalWin(float $total_win): void
    {
        $this->total_win = $total_win;
    }

    public function getLoginDate(): Carbon
    {
        return $this->login_date;
    }

    public function setLoginDate(Carbon $login_date): void
    {
        $this->login_date = $login_date;
    }

    public function getLogoutDate(): Carbon
    {
        return $this->logout_date;
    }

    public function setLogoutDate(Carbon $logout_date): void
    {
        $this->logout_date = $logout_date;
    }

    public function getLastActiveDate(): Carbon
    {
        return $this->last_active_date;
    }

    public function setLastActiveDate(Carbon $last_active_date): void
    {
        $this->last_active_date = $last_active_date;
    }

    public function getOuterTable(): string
    {
        return $this->outer_table;
    }

    public function setOuterTable(string $outer_table): void
    {
        $this->outer_table = $outer_table;
    }

    public function getOuterId(): int
    {
        return $this->outer_id;
    }

    public function setOuterId(int $outer_id): void
    {
        $this->outer_id = $outer_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getBonusId(): int
    {
        return $this->bonus_id?? 0;
    }

    public function setBonusId(int $bonus_id): void
    {
        $this->bonus_id = $bonus_id;
    }

    public static function getUserTopGame(int $user_id)
    {
        $query = UserGameStat::query();
        $sql = "count(user_game_stats.id) as played_count, user_game_stats.game_id as game_id, game_configs.name as game_name,
         user_game_stats.`system` as gsystem, user_game_stats.`group` as ggroup";
        $query->where('user_id', $user_id);
        $query->groupBy('game_id');
        $query->groupBy('game_configs.name');
        $query->groupBy('user_game_stats.system');
        $query->groupBy('user_game_stats.group');
        $query->orderBy('played_count', 'desc');
        $query->leftJoin('game_configs', 'game_configs.game_id', 'user_game_stats.game_id');

        $game_stats = $query->selectRaw($sql)->take(1)->get();
        return $game_stats->first();
    }

    public static function getUserTopProvider(int $user_id)
    {
        $query = UserGameStat::query();
        $sql = "count(user_game_stats.id) as played_count, user_game_stats.`group` as group_id, user_game_stats.`system` as gsystem";
        $query->where('user_id', $user_id);
        $query->groupBy('user_game_stats.group');
        $query->groupBy('user_game_stats.system');
        $query->orderBy('played_count', 'desc');

        $game_stats = $query->selectRaw($sql)->take(1)->get();
        return $game_stats->first();
    }
}
