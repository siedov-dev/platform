<?php

namespace App\Http\Controllers;

use App\Models\Tournaments\Custom\CustomTournament;
use App\Models\Tournaments\GoTournament;
use App\Models\Tournaments\TournamentPromo\TournamentPromo;
use App\MUser;
use App\Services\Tournaments\TournamentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.tournamentsTitle');
        $this->metaTags->description = __('meta.tournamentsDesc');
        $tournaments = [];
        $completed_tournaments = [];

        $service = new TournamentsService(MUser::getAuth());
        if ($service->tournamentsEnabled()) {
            $tournaments = $service->getAvailableTournaments();
            $completed_tournaments = $service->getCompletedTournaments();
        }

        $this->renderAdaptive('tournaments.index', [
            'active_tournaments' => $tournaments,
            'completed_tournaments' => $completed_tournaments,
        ], []);
        return $this->sendPreparedResponse();
    }

    public function info(string $locale, string $tournament_id, Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $view = 'tournaments.info';
        $tournament = CustomTournament::getById($tournament_id);
        if ($tournament !== null) {
            $view = 'tournaments.custom.' . $tournament->getId() . '/' . app()->getLocale();
            $this->renderAdaptive($view, ['tournament' => $tournament], []);
        } else {
            $s = new TournamentsService(MUser::getAuth());
            if ($s->tournamentsEnabled()) {
                /** @var GoTournament $tournament */
                $tournament = $s->getTournament($tournament_id, MUser::getAuth()?->currency ?? 0);
                if ($tournament !== null) {
                    $table = $tournament->getWinnersTable();
                    $prizes = $tournament->getPrizes();
                    $conditions = $tournament->getConditions(App::getLocale());
                    $user_place = 0;
                    // user position
                    if (Auth::check()) {
                        $user_id = Auth::id();
                        foreach ($table as $place => $vals) {
                            if ($vals['points'] > 0 && $vals['user_id'] == $user_id) {
                                $user_place = $place;
                                break;
                            }
                        }
                    }
                    $this->renderAdaptive($view, [
                        'tournament' => $tournament,
                        'table' => $table,
                        'prizes' => $prizes,
                        'conditions' => $conditions,
                        'user_place' => $user_place,
                    ], []);
                }
            }
        }

        return $this->sendPreparedResponse();
    }

    public function promoInfo(string $locale, string $tournament_id, Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $tournament_promo = TournamentPromo::findOrFail($tournament_id);

        $this->renderAdaptive('tournaments.promo', ['tournament' => $tournament_promo], []);
        return $this->sendPreparedResponse();
    }

    public function participate(Request $request)
    {
        $s = new TournamentsService();
        if ($s->tournamentsEnabled() && Auth::check()) {
            $resp = $s->addParticipant($request->input('tournament_id'), Auth::id());
            if ($resp !== "ok") {
                Log::error('TournamentsController participate: ' . $resp);;
            }
        }
        return back();
    }

    public function gameTableData(Request $request)
    {
        $s = new TournamentsService(MUser::getAuth());
        $table = $s->getWinners($request->input('tournament_id'), 10);
        $user_position = 0;
        // user position
        $user_id = Auth::id();
        $player = [];
        foreach ($table as $place => $vals) {
            $table[$place]['login'] = MUser::hideLogin($vals['login']);
            if ($vals['points'] > 0 && $vals['user_id'] == $user_id) {
                $user_position = $place;
            }
        }
        if ($user_id > 0 && $user_position === 0) {
            $position = $s->getUserPosition($request->input('tournament_id'), $user_id);
            $user_position = $position['position']?? 0;
            $player = $position['player']?? [];
        }
        if ($user_position > 10) {
            unset($table[10]);
            $player['login'] = MUser::hideLogin($player['login']);
            $table[$user_position] = $player;
        }
        $data = [
            'table' => $table,
            'user_position' => $user_position,
        ];
        return response()->json($data);
    }
}
