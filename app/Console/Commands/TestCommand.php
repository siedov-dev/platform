<?php

namespace App\Console\Commands;

use App\GamesProviders\TVBet\TVBetGamesProviderApi;
use App\Helpers\Localization;
use App\UserWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mytest';

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
        $this->info('Start testing...');
        $locale = 'ru';
        $currency = Localization::getLocaleCurrency($locale);
        $filter_where = ' and hall_id = ' . UserWallet::currencyHallId($currency);
        $filter_where .= ' and login_date > "' . now()->subMonths(2) . '" ';
        $query = "
                                SELECT game_id, count(id) as starts, 'qt' as provider, CONCAT('qt/', game_id) as guid
                                FROM qt_game
                                WHERE 1=1
                                $filter_where
                                GROUP BY game_id

                                UNION SELECT game_id, count(id) as starts, 'pragplay' as provider, CONCAT('pragplay/', game_id) as guid
                                FROM prag_games
                                WHERE 1=1
                                $filter_where
                                GROUP BY game_id

                                UNION SELECT game_id, count(id) as starts, 'sleg' as provider, CONCAT('sleg/', game_id) as guid
                                FROM slerg_games
                                WHERE 1=1
                                $filter_where
                                GROUP BY game_id

                                ORDER BY starts DESC
                                LIMIT 60
                                ";

        $result = DB::select(DB::raw($query));

        $data = json_decode(json_encode($result), true);
        $this->info('data '.var_export($data, true));
        $json = json_encode(collect($data)->pluck('starts', 'guid')->toArray());
        $this->info('popular_gids_' . $locale ." :".$json);

        return 0;
    }
}
