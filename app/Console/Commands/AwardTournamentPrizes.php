<?php

namespace App\Console\Commands;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserInventory;
use App\Models\Tournaments\GoTournament;
use App\Models\Tournaments\TournamentAwardSlcItem;
use App\Models\TournamentWinLog;
use App\MUser;
use App\Services\Tournaments\TournamentsService;
use App\UserWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AwardTournamentPrizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:awards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process completed tournaments awards';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $s = new TournamentsService();
            if (!$s->tournamentsEnabled()) return Command::SUCCESS;

            // pending award tournaments
            $pa_tournament = $s->getPendingAwardTournaments();


            /** @var GoTournament $t */
            foreach ($pa_tournament as $t) {

                if ($t->setStatus(GoTournament::STATUS_DONE) !== 'ok') {
                    Log::error('tournament:awards: error tournament status change');;
                    continue;
                }

                $winners = $s->getWinners($t->getId());
                foreach ($s->getPrizes($t->getId(), UserWallet::CURRENCY_EUR) as $i => $p) {
                    if (!empty($winners[$i]['user_id']) && $winners[$i]['points'] > 0) {
                        // check if the user made enough spins
                        if ($t->getSpinsRequired() > 0 && $winners[$i]['spins_count'] < $t->getSpinsRequired()) {
                            Log::error("tournament:awards: not enough spins for user #{$winners[$i]['user_id']} in tournament #{$t->getId()}");
                            continue;
                        }
                        /** @var MUser $user */
                        $user = MUser::find($winners[$i]['user_id']);
                        if ($user !== null) {
                            // test tournaments give prizes to testers only
                            if ($t->isTestMode() && !$user->isTestUser()) continue;

                            $prizes = $s->getPrizes($t->getId(), $user->getCurrency());

                            $log = new TournamentWinLog();
                            $log->setTournamentId($t->getId());
                            $log->setUserId($user->getId());
                            $log->setPosition($i);
                            $log->setWinAmount($prizes[$i]['money_amount']?? 0);
                            $log->setCurrency($user->getCurrencyName());
                            $log->setItemEval($prizes[$i]['item']['item_eval']?? 0);
                            $log->save();

                            $item = new TournamentAwardSlcItem($i, $prizes[$i]['money_amount']?? 0, $user->getCurrencyName(), $prizes[$i]['item']?? []);
                            $item->setImg($prizes[$i]['img']?? '');
                            $item->setTexts($prizes[$i]['texts']?? '');
                            $inv = SlcUserInventory::forUser($user);
                            $inv->addItem($item, "tournament #".$t->getId());

                            try {
                                $c_event_data = [
                                    'tournament_id' => $t->getId(),
                                    'position' => $i,
                                    'money' => $prizes[$i]['money_amount']?? 0,
                                    'currency' => $user->getCurrencyName(),
                                    'tournament_name' => $t->getName(),
                                ];
                                if (!empty($prizes[$i]['item'])) {
                                    $c_event_data['item_type'] = SlcItemType::tryFrom($prizes[$i]['item']['item_type'])?->name?? '';
                                    $c_event_data['item_amount'] = $prizes[$i]['item']['amount']?? 0;
                                    Log::info('award item', $prizes[$i]['item']);
                                    if ($prizes[$i]['item']['item_type'] === 0 && $prizes[$i]['item']['bonus_type'] > 0) {
                                        $bonus_params = json_decode($prizes[$i]['item']['bonus_params'], true);
                                        if (!empty($bonus_params['spins'])) {
                                            $c_event_data['FS'] = $bonus_params['spins'];
                                        }
                                        if (!empty($bonus_params['freespins'])) {
                                            $c_event_data['FS'] = $bonus_params['freespins'];
                                        }
                                    }
                                }
                                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::TournamentAward, $c_event_data);
                            } catch (\Exception $e) {
                                Hlog::logException($e);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return Command::SUCCESS;
    }
}
