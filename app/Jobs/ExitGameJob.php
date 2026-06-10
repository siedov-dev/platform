<?php

namespace App\Jobs;

use App\Events\MoneySpent;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Models\GameTransaction;
use App\Models\Popups\UserPopup;
use App\Models\Rank\UserRankLog;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\TopWin;
use App\Models\UserGameStat;
use App\Models\UserStatistics\StatisticsType;
use App\Models\UserStatistics\UserStatistics;
use App\MUser;
use App\Redis\DeferredModelWriter;
use App\UserWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @note reload the queue service if changed!
 * send game results to partners
 * @deprecated
 */
class ExitGameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $system;
    private string $game_data_class;
    private int $game_data_id;
    private string $currency;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $system, string $game_data_class, int $game_data_id, string $currency)
    {
        $this->system = $system;
        $this->game_data_class = $game_data_class;
        $this->game_data_id = $game_data_id;
        $this->currency = $currency;
        $this->onQueue('game');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info(__CLASS__ . ' game complete background data operations ' . $this->currency . $this->system . $this->game_data_id);
        try {
            $data_model = ($this->game_data_class)::where('id', $this->game_data_id)->lockForUpdate()->first();
            if ($data_model === null) {
                Log::error(__CLASS__ . ' data not found');
                return;
            }
            DB::beginTransaction();

            //check data already exists
            $old_data = UserGameStat::where('system', $this->system)
                ->where('outer_id', $this->game_data_id)->first();
            if ($old_data !== null) {
                Log::error(__CLASS__ . ' data already processed');
                return;
            }

            $user_game = new UserGameStat();
            $user_game->user_id = $data_model->user_id;
            $user_game->currency = $this->currency;
            $user_game->system = $this->system;
            $user_game->session_id = $data_model->session_id;
            $user_game->game_id = $data_model->game_id;
            $user_game->login_balance = $data_model->login_balance;
            $user_game->logout_balance = $data_model->logout_balance;
            $user_game->balance_diff = $data_model->login_balance - $data_model->logout_balance;
            $user_game->login_date = $data_model->login_date;
            $user_game->logout_date = $data_model->logout_date;
            $user_game->last_active_date = $data_model->last_active_date ?? null;
            $user_game->outer_table = $data_model->getTable();
            $user_game->outer_id = $data_model->id;
            $user_game->save();
            DB::commit();

            DeferredModelWriter::getForModel(GameTransaction::class)->writeToDb(true);

            $provider = GamesManager::getProvider($this->system);
            $game = $provider?->getGame('', $user_game->game_id, true);
            $spentAmount = $user_game->login_balance - $user_game->logout_balance;
            /** @var MUser $user */
            $user = MUser::findOrFail($user_game->user_id);

            $full_total_bet = GameTransaction::where('user_id', $user->id)
                ->where('currency', $this->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', 'bet')
                ->sum('amount');

            $full_total_win = GameTransaction::where('user_id', $user->id)
                ->where('currency', $this->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', '!=', 'bet')
                ->sum('amount');

            $total_bet = GameTransaction::where('user_id', $user->id)
                ->where('currency', $this->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', 'bet')
                ->where(function ($q) {
                    $q->whereNull('bonus');
                    $q->orWhere('bonus', 0);
                })
                ->sum('amount');

            $total_win = GameTransaction::where('user_id', $user->id)
                ->where('currency', $this->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where(function ($q) {
                    $q->whereNull('bonus');
                    $q->orWhere('bonus', 0);
                })
                ->where('action', '!=', 'bet')
                ->sum('amount');

            $user_game->total_bet = $total_bet;
            $user_game->full_total_bet = $full_total_bet;
            $user_game->real_total_bet = $total_bet;

            $user_game->total_win = $total_win;
            $user_game->full_total_win = $full_total_win;
            $user_game->real_total_win = $total_win;
            $user_game->status = UserGameStat::STATUS_OK;
            $user_game->group = $game?->getGroupId();

            $total_diff = $user_game->balance_diff - ($user_game->real_total_bet - $user_game->real_total_win);

            //if bonus win on some transaction lost - compensate
            if ($total_diff !== 0) {
                if ($total_diff > 0) {
                    $user_game->total_bet += abs($total_diff);
                } else {
                    $user_game->total_win += abs($total_diff);
                }
                $user_game->status = UserGameStat::STATUS_MODIFIED;
            }
            $user_game->save();

            event(new MoneySpent(
                user: $user,
                amount: $spentAmount,
                currency: $this->currency,
                source: 'game',
                game: $game,
                total_bet: $user_game->total_bet,
                total_win: $user_game->total_win)
            );

            if ($game?->isCrash()) {
                UserStatistics::updateStats($user, StatisticsType::WinBetsInCrashGames);
            }
            if ($full_total_bet > 0) {
                UserStatistics::updateStats($user, StatisticsType::MadeBets);
                UserStatistics::updateStats($user, StatisticsType::MaxBetMultiplier);
            }

            $curr_rank = $user->getRank(UserWallet::getCurrencyCode($this->currency))->getLevel();
            //if rank has changed
            if ($user->rank != $curr_rank) {
                //save log
                UserRankLog::log($user->getId(), UserWallet::getCurrencyCode($this->currency), $user->rank, $curr_rank);
                $user->rank = $curr_rank;
                $user->save();
                //show notify popup
                UserPopup::showNewRank($user, $curr_rank);
            }

            SlcAchievement::updateAchievements($user);

            TopWin::userWin($user, $spentAmount * -1, $user_game->game_id, UserWallet::getCurrencyCode($this->currency), $this->system, $game?->getGroupId());
        } catch (\Throwable $e) {
            DB::commit();
            $this->fail();
            Log::error(__CLASS__ . $e->getMessage());
            Log::error(__CLASS__ . $e->getFile());
            Log::error(__CLASS__ . $e->getLine());
            Log::error(__CLASS__ . $e->getTraceAsString());
        } finally {
            DB::commit();
        }
    }
}
