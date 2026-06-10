<?php

namespace App\Jobs;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Events\MoneySpent;
use App\GamesProviders\GamesManager;
use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusPatterns;
use App\Models\Rank\UserRankLog;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\UserTasks\UserTaskType;
use App\Models\TopWin;
use App\Models\UserStatistics\StatisticsType;
use App\Models\UserStatistics\UserStatistics;
use App\MUser;
use App\Services\IpInfo\IpInfo;
use App\Services\KSlc\KSlc;
use App\UserWallet;
use App\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @note reload the queue service if changed!
 * send game results to partners
 */
class ExitGoGameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $system;
    private string $game_id;
    private string $currency;
    private float $total_bet;
    private float $total_win;
    private float $login_balance;
    private float $logout_balance;
    private int $user_id;
    private int $bonus_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, string $system, string $game_id, string $currency,
                                float $total_bet, float $total_win, float $login_balance, float $logout_balance, ?int $bonus_id = 0)
    {
        $this->user_id = $user_id;
        $this->system = $system;
        $this->currency = $currency;
        $this->game_id = $game_id;
        $this->total_win = $total_win;
        $this->total_bet = $total_bet;
        $this->login_balance = $login_balance;
        $this->logout_balance = $logout_balance;
        $this->bonus_id = $bonus_id?? 0;
        $this->onQueue('game');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(__CLASS__ . ' go game complete background data operations ' . $this->currency . $this->system . $this->game_id);
        try {
            DB::beginTransaction();

            $provider = GamesManager::getProvider($this->system);
            $game = $provider?->getGame('', $this->game_id, true);
            $spentAmount = $this->total_bet - $this->total_win;
            /** @var MUser $user */
            $user = MUser::findOrFail($this->user_id);

            try {
                // отписываемся от нотификаций изменения таблицы лидеров
                $ts = new \App\Services\Tournaments\TournamentsService($user);
                if ($ts->tournamentsEnabled()) {
                    $tournament = $ts->getGameTournament($game->getProviderId(), $game->getId());
                    if ($tournament) $ts->unsubscribeNotifications($tournament->getId(), $user->getId());
                }
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }

            event(new MoneySpent(
                user: $user,
                amount: $spentAmount,
                currency: $this->currency,
                source: 'game',
                game: $game,
                total_bet: $this->total_bet,
                total_win: $this->total_win)
            );

            try {
                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::CloseGame, [
                    'provider' => $game?->getProviderId(),
                    'total_bet' => $this->total_bet,
                    'total_win' => $this->total_win,
                    'money_won' => $this->total_win - $this->total_bet,
                    'currency' => $this->currency,
                ]);
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }

            try {
                if ($game === null) {
                    Log::error(__CLASS__ . ' exit game not found game '.$this->game_id.' provider '.$this->system);
                } else {
                    $profit = $this->logout_balance - $this->login_balance;
                    if ($profit != 0) {
                        $ev = $profit > 0 ? CioEventType::GameWin : CioEventType::GameLose;
                        $ip_info = IpInfo::getInfo($user->last_ip);
                        CustomerIo::getInstance()->sendEvent($user->getId(), $ev, [
                            'provider' => $game->getProviderId(),
                            'gameId' => $game->getId(),
                            'gameName' => $game->getName(),
                            'currency' => $this->currency,
                            'amount' => abs($profit),
                            'country' => $ip_info?->country_code,
                            'city' => $ip_info?->city,
                            'locale' => $user->getLocale(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Hlog::logException($e);
            }

            if ($game?->isCrash()) {
                UserStatistics::updateStats($user, StatisticsType::WinBetsInCrashGames);
            }
            if ($this->total_bet > 0) {
                UserStatistics::updateStats($user, StatisticsType::MadeBets);
                UserStatistics::updateStats($user, StatisticsType::MaxBetMultiplier);
            }
            try {
                KSlc::getInstance()->dailyTasksCheckTaskByType($user->getId(), UserTaskType::MakeBet, $this->currency);
                KSlc::getInstance()->dailyTasksCheckTaskByType($user->getId(), UserTaskType::GetMult, $this->currency);
            } catch (\Exception $e) {
                Hlog::logException($e);
            }

            $curr_rank = $user->getRank(UserWallet::getCurrencyCode($this->currency))->getLevel();
            Log::info(__CLASS__." current rank $curr_rank user rank ".$user->rank);
            //if rank has changed
            if ($user->rank != $curr_rank) {
                $old_rank = $user->rank;
                //save log
                UserRankLog::log($user->getId(), UserWallet::getCurrencyCode($this->currency), $user->rank, $curr_rank);
                $user->rank = $curr_rank;
                $user->save();
                //show notify popup
//                UserPopup::showNewRank($user, $curr_rank);
                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::NewRank, [
                    'rank' => $curr_rank,
                    'old_rank' => $old_rank,
                ]);
            }

            // welcome dep bonus completed check
            try {
                if ($this->bonus_id > 0) {
                    /** @var UserBonus $bonus */
                    $bonus = UserBonus::find($this->bonus_id);
                    if ($bonus !== null && $bonus->pattern_id > 0) {
                        /** @var BonusPatterns $ptn */
                        $ptn = BonusPatterns::find($bonus->pattern_id);
                        if ($ptn !== null && preg_match('#^bonus.dep_bonus_(d).*$#', $ptn->getName(), $matches)) {
                            $bonus = UserBonus::convertByType($bonus);
                            if ($bonus->isCompleted()) {
                                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::WbDepBonusDone, [
                                    'bonus' => $matches[1],
                                ]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }

            SlcAchievement::updateAchievements($user);

            TopWin::userWin($user, $spentAmount * -1, $game->getId(), UserWallet::getCurrencyCode($this->currency), $this->system, $game?->getGroupId());

        } catch (\Throwable $e) {
            $this->fail();
            Hlog::logException($e);
        } finally {
            DB::commit();
        }
    }
}
