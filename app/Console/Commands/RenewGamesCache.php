<?php

namespace App\Console\Commands;

use App\Models\LandingGamesForCurrency;
use App\Services\Games\GameManagerApi;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewGamesCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewGamesCache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('--------- ' . date('d.m.Y H:i:s') . ' ---------');

        $currencies = Wallet::activeCurrencies();
        $devices = [Device::DESKTOP, Device::PHONE,];

        $this->info('updating providers group names cache');
        $providers = GamesManager::getAllProviders();
        foreach ($providers as $provider) {
            $t = microtime(true) * 1000;
            $this->info($provider->getId());
            $provider->cacheGroupNames();
            $this->info(str_pad($provider->getId(), 10, ' ') . round((microtime(true) * 1000 - $t)) . ' milliseconds.'
            );
        }

        $this->info('updating providers caches');
        foreach ($providers as $provider) {
            $t = microtime(true) * 1000;
            $this->info($provider->getId());
            $provider->cacheGames();
            $this->info(str_pad($provider->getId(), 10, ' ') . round((microtime(true) * 1000 - $t)) . ' milliseconds.'
            );
        }

        try {
            $this->info('cache popular game ids');
            //all data
            $filter_where = ' and login_date > "' . now()->subMonths(2) . '" ';
            $query = "
                                SELECT game_id, count(id) as starts, `system` as provider, CONCAT(`system`, '/', game_id) as guid
                                FROM user_game_stats
                                WHERE 1
                                $filter_where
                                GROUP BY `system`, game_id
                                ORDER BY starts DESC
                                LIMIT 60
                                ";
            $result = DB::select(DB::raw($query));

            $data = json_decode(json_encode($result), true);
            $json = json_encode(collect($data)->pluck('starts', 'guid')->toArray());
            $this->info('popular_gids_' . " :" . $json);
//                Cache::forever('popular_gids_' . $curr_name, $json);

            //store games in landing games config

            //clear old games
            LandingGamesForCurrency::where('type', 'top')->where('currency', "")->delete();

            //add additional manual (plus) games
            foreach (LandingGamesForCurrency::where('type', 'plustop')->where('currency', "")->get() as $plus_game) {
                $lg = new LandingGamesForCurrency();
                $lg->type = 'top';
                $lg->currency = "";
                $lg->cid = $plus_game->cid;
                $lg->save();
            }

            foreach ($data as $game_stat) {
                $system_id = explode('/', $game_stat['guid'])[0];
                $game_id = explode('/', $game_stat['guid'])[1];
                $system = GamesManager::getProvider($system_id);
                if ($system !== null) {
                    $game = $system->getGame('', $game_id);
                    //if not already in list
                    if ($game && LandingGamesForCurrency::where('type', 'top')->where('currency', "")->where('cid', $game->getCompoundId())->first() === null) {
                        $lg = new LandingGamesForCurrency();
                        $lg->type = 'top';
                        $lg->currency = "";
                        $lg->cid = $game->getCompoundId();
                        $lg->save();
                    }
                }
            }
            ///

            foreach ($currencies as $currency_id) {
                $curr_name = Wallet::currencyName($currency_id);
                $filter_where = ' and currency = "' . $curr_name. '"';
                $filter_where .= ' and login_date > "' . now()->subMonths(2) . '" ';

                $query = "
                                SELECT game_id, count(id) as starts, `system` as provider, CONCAT(`system`, '/', game_id) as guid
                                FROM user_game_stats
                                WHERE 1
                                $filter_where
                                GROUP BY `system`, game_id
                                ORDER BY starts DESC
                                LIMIT 60
                                ";
                $result = DB::select(DB::raw($query));

                $data = json_decode(json_encode($result), true);
                $json = json_encode(collect($data)->pluck('starts', 'guid')->toArray());
                $this->info('popular_gids_' . $curr_name . " :" . $json);
//                Cache::forever('popular_gids_' . $curr_name, $json);

                //store games in landing games config

                //clear old games
                LandingGamesForCurrency::where('type', 'top')->where('currency', $curr_name)->delete();

                //add additional manual (plus) games
                foreach (LandingGamesForCurrency::where('type', 'plustop')->where('currency', $curr_name)->get() as $plus_game) {
                    $lg = new LandingGamesForCurrency();
                    $lg->type = 'top';
                    $lg->currency = $curr_name;
                    $lg->cid = $plus_game->cid;
                    $lg->save();
                }

                foreach ($data as $game_stat) {
                    $system_id = explode('/', $game_stat['guid'])[0];
                    $game_id = explode('/', $game_stat['guid'])[1];
                    $system = GamesManager::getProvider($system_id);
                    if ($system !== null) {
                        $game = $system->getGame('', $game_id);
                        //if not already in list
                        if ($game && LandingGamesForCurrency::where('type', 'top')->where('currency', $curr_name)->where('cid', $game->getCompoundId())->first() === null) {
                            $lg = new LandingGamesForCurrency();
                            $lg->type = 'top';
                            $lg->currency = $curr_name;
                            $lg->cid = $game->getCompoundId();
                            $lg->save();
                        }
                    }
                }
            }

            $this->info('cache hot game ids');
            // all data
            $curr_name = "";

            $filter_where = ' and login_date > "' . now()->subDays(1) . '" ';

            $query = "
                                SELECT game_id, count(id) as starts, `system` as provider, CONCAT(`system`, '/', game_id) as guid
                                FROM user_game_stats
                                WHERE 1
                                $filter_where
                                GROUP BY `system`, game_id
                                ORDER BY starts DESC
                                LIMIT 60
                                ";

            $result = DB::select(DB::raw($query));

            $data = json_decode(json_encode($result), true);
            $json = json_encode(collect($data)->pluck('starts', 'guid')->toArray());
            $this->info('hot_gids_' . $curr_name . " :" . $json);
//                Cache::forever('hot_gids_' . $locale, $json);

            //store games in landing games config
            //clear old games
            LandingGamesForCurrency::where('type', 'hot')->where('currency', $curr_name)->delete();

            //add additional manual (plus) games
            foreach (LandingGamesForCurrency::where('type', 'plushot')->where('currency', $curr_name)->get() as $plus_game) {
                $lg = new LandingGamesForCurrency();
                $lg->type = 'hot';
                $lg->currency = $curr_name;
                $lg->cid = $plus_game->cid;
                $lg->save();
            }

            foreach ($data as $game_stat) {
                $system_id = explode('/', $game_stat['guid'])[0];
                $game_id = explode('/', $game_stat['guid'])[1];
                $system = GamesManager::getProvider($system_id);
                if ($system !== null) {
                    $game = $system->getGame('', $game_id);
                    if ($game && LandingGamesForCurrency::where('type', 'hot')->where('currency', $curr_name)->where('cid', $game->getCompoundId())->first() === null) {
                        $lg = new LandingGamesForCurrency();
                        $lg->type = 'hot';
                        $lg->currency = $curr_name;
                        $lg->cid = $game->getCompoundId();
                        $lg->save();
                    }
                }
            }

            foreach ($currencies as $currency) {
                $curr_name = Wallet::currencyName($currency);

                $filter_where = ' and currency = "' . $curr_name. '"';
                $filter_where .= ' and login_date > "' . now()->subDays(1) . '" ';

                $query = "
                                SELECT game_id, count(id) as starts, `system` as provider, CONCAT(`system`, '/', game_id) as guid
                                FROM user_game_stats
                                WHERE 1
                                $filter_where
                                GROUP BY `system`, game_id
                                ORDER BY starts DESC
                                LIMIT 60
                                ";

                $result = DB::select(DB::raw($query));

                $data = json_decode(json_encode($result), true);
                $json = json_encode(collect($data)->pluck('starts', 'guid')->toArray());
                $this->info('hot_gids_' . $curr_name . " :" . $json);
//                Cache::forever('hot_gids_' . $locale, $json);

                //store games in landing games config
                //clear old games
                LandingGamesForCurrency::where('type', 'hot')->where('currency', $curr_name)->delete();

                //add additional manual (plus) games
                foreach (LandingGamesForCurrency::where('type', 'plushot')->where('currency', $curr_name)->get() as $plus_game) {
                    $lg = new LandingGamesForCurrency();
                    $lg->type = 'hot';
                    $lg->currency = $curr_name;
                    $lg->cid = $plus_game->cid;
                    $lg->save();
                }

                foreach ($data as $game_stat) {
                    $system_id = explode('/', $game_stat['guid'])[0];
                    $game_id = explode('/', $game_stat['guid'])[1];
                    $system = GamesManager::getProvider($system_id);
                    if ($system !== null) {
                        $game = $system->getGame('', $game_id);
                        if ($game && LandingGamesForCurrency::where('type', 'hot')->where('currency', $curr_name)->where('cid', $game->getCompoundId())->first() === null) {
                            $lg = new LandingGamesForCurrency();
                            $lg->type = 'hot';
                            $lg->currency = $curr_name;
                            $lg->cid = $game->getCompoundId();
                            $lg->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());
        }

//        foreach ($devices as $device) {
//            Device::setDevice($device);
//            $this->info('device: ' . $device);
//            foreach ($locales as $locale) {
//                App::setLocale($locale);
//                $this->info("\n");
//                $this->info('Locale: ' . $locale);
//                GamesManager::reset();
//                $providers = GamesManager::getAllProviders();
//                foreach ($providers as $provider) {
//                    $t = microtime(true) * 1000;
//                    $provider->restoreAllGames(false);
//                    $this->info(str_pad($provider->getId(), 10, ' ')
//                        . ' ' . $provider->getTotalGamesCount() . ' games in '
//                        . round((microtime(true) * 1000 - $t)) . ' milliseconds.'
//                    );
//                }
//            }
//        }


        $this->info("\n");

        //reload games
        try {
            $this->info("reloading games: \n");
            $this->info(GameManagerApi::getInstance()->reload());
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }
}
