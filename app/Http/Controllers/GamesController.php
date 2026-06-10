<?php

namespace App\Http\Controllers;

use App\Models\UserValue;
use App\Services\Games\GameManagerApi;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GameTag;
use App\Helpers\MetaTags;
use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Services\Users\UserManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GamesController extends Controller
{
    public function index(Request $request)
    {
        Log::info('games_index', $request->all());

        //if not ajax send page loader
        if (!$request->ajax()) {
            $data = $request->all();
            //reset page
            $data['page'] = 0;
            Log::info('Not ajax games search', $data);
            $this->renderAdaptive('page_loader', ['url' => route($request->route()->getName(), $data, false)], ['layouts.main']);
            return $this->sendPreparedResponse();
        }
        // Providers and groups.
        $providers = [];
        $groups = [];
        $provider = null;
        $p = $request->get('providers', '');
        $p = explode(',', $p);
        foreach ($p as $d) {
            $a = explode('|', $d);
            if (count($a) === 2) {
                $provider = $a[0];
                $groups [] = $a[1];
            }
        }
        $providers = array_unique($providers);
        $groups = array_unique($groups);

        // Categories (null - any category).
        $c = $request->get('category', null);
        $categories = $c === null ? null : explode(',', $c);
        $tags = [];
        if ($categories === null) {
            switch ($request->route()->getName()) {
                case 'games.slots':
                    $categories = ['slots'];
                    break;
                case 'games.table':
                    $categories = ['table'];
                    break;
                case 'games.live':
                    $categories = ['live'];
                    break;
                case 'games.sport':
                    $categories = ['sport'];
                    break;
                case 'games.bonus':
                    $categories = ['bonus'];
                    break;
                case 'games.tournament':
                    $categories = ['tournament'];
                    break;
                case 'games.crash':
                    $tags = ['crash'];
                    break;
                case 'games.new':
                    $tags = ['new'];
                    $user = MUser::getAuth();
                    if ($user !== null) {
                        $kgames = GameManagerApi::getInstance();
                        $hash = $kgames->newGamesHash();
                        $user->setValue('new_games_hash', $hash);
                    }
                    break;
                case 'games.top':
                    $tags = ['top'];
                    break;
                case 'games.hot':
                    $tags = ['hot'];
                    break;
                case 'games.megaways':
                    $tags = ['megaways'];
                    break;
                case 'games.fs':
                    $tags = ['fs'];
                    break;
                case 'games.jackpot':
                    $tags = ['jackpot'];
                    break;
            }
        }

        $search_cats = $categories;
        $onlyTournamentGames = $categories !== null && in_array('tournament', $categories);
        $onlyBonusGames = $categories !== null && in_array('bonus', $categories);
        if ($onlyBonusGames) {
            $onlyTournamentGames = false;
            unset($categories['tournament']);
        }
        if ($categories !== null && $categories[0] === 'bonus' && count($categories) === 1) $search_cats = null;
        if ($categories !== null && $categories[0] === 'tournament' && count($categories) === 1) $search_cats = null;
        $no_active_bonus = false;

        $lnd_category = null;
        //add crash games if selected

        $name = $request->get('name', '');
        $bonus_groups = [];
        $bonus = null;

        if ($onlyBonusGames) {
            $user = MUser::getAuth();
            $bonus = $user?->getActiveBonus();
        }
        $page = intval($request->get('page', 1));

        if ($page === 0) $page = 1;

        if ($onlyBonusGames && $bonus === null) {
            $no_active_bonus = true;
        }

        if ($onlyBonusGames && $bonus !== null) {
            UserValue::setValue(MUser::getAuth()->getId(), 'active_bonus', $bonus->getId());
            if (!empty($bonus->allowed_games)) {
                $games = GameManagerApi::getInstance()->bonusGames(
                    bonus_id: $bonus->getId(),
                    groups: $groups ?? [],
                    categories: $search_cats ?? [],
                    tags: $tags ?? [],
                    name: $name ?? '',
                    per_page: $request->get('pageSize', 60),
                    page: $page,
                );
                $bonus_groups = array_keys($games->getGroups());
            } else {
                if (!empty($bonus->wager_games_segment_id)) {
                    $games = GameManagerApi::getInstance()->getGameSegmentsGames(
                        segment_id: $bonus->wager_games_segment_id,
                        groups: $groups ?? [],
                        categories: $search_cats ?? [],
                        tags: $tags ?? [],
                        name: $name ?? '',
                        per_page: $request->get('pageSize', 60),
                        page: $page,
                    );
                } else {
                    $games = GameManagerApi::getInstance()->searchGames(
                        groups: $groups ?? [],
                        categories: $search_cats ?? [],
                        tags: $tags ?? [],
                        name: $name ?? '',
                        per_page: $request->get('pageSize', 60),
                        page: $page,
                        for_bonus_type: $bonus->type,
                    );
                }
                $bonus_groups = array_keys($games->getGroups());
            }
        } elseif ($onlyTournamentGames) {
            $user = MUser::getAuth();
            if ($user !== null) {
                $kgames = GameManagerApi::getInstance();
                $t_hash = $kgames->tournamentGamesHash();
                $user->setValue('tnt_games_hash', $t_hash);
            }

            $games = GameManagerApi::getInstance()->searchTournamentGames(
                groups: $groups ?? [],
                categories: $search_cats ?? [],
                tags: $tags ?? [],
                name: $name ?? '',
                per_page: $request->get('pageSize', 60),
                page: $page);
        } else {
            $games = GameManagerApi::getInstance()->searchGames(
                groups: $groups ?? [],
                categories: $search_cats ?? [],
                tags: $tags ?? [],
                name: $name ?? '',
                per_page: $request->get('pageSize', 60),
                page: $page);
        }

        if (is_array($categories)) $categories = array_unique($categories);

        $groups_available = $games->getGroups();

        if ($request->get('itemsOnly', '0') === '1') {
            $this->renderAdaptive('games.items', [
                'games' => $games,
                'bonus_groups' => $bonus_groups,
                'onlyBonusGames' => $onlyBonusGames,
                'categories' => $categories,
                'bonus' => $bonus,
            ]);
            $this->renderAdaptive('games.show-more', ['games' => $games]);
        } else {
            $prefix = substr($request->route()->getName(), 6);
            $this->metaTags = new MetaTags();
            $this->metaTags->title = __('meta.' . $prefix . 'Title');
            $this->metaTags->description = __('meta.' . $prefix . 'Description');
            $this->metaTags->keywords = __('meta.' . $prefix . 'slotsKeywords');
            $this->renderAdaptive('games.index', [
                'games' => $games,
                'categories' => array_merge($categories?? [], $tags?? []),
                'bonus' => $bonus,
                'bonus_groups' => $bonus_groups,
                'onlyBonusGames' => $onlyBonusGames,
                'onlyTournamentGames' => $onlyTournamentGames,
                'groupsAvailable' => $groups_available,
                'no_active_bonus' => $no_active_bonus,
            ], request()->ajax() ? [] : ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function searchGames(Request $request)
    {
        Log::info('games search', $request->all());
        $page = intval($request->get('page', 1));
        if ($page === 0) $page = 1;

        // Providers and groups.
        $providers = [];
        $groups = [];
//        Log::info(var_export($request->all(), true));

        $name = $request->get('name', '');

        if (empty($name)) {
//            $games = GamesManager::getGamesByTag('top');
            $games = GameManagerApi::getInstance()->searchGames(
                groups: [],
                categories: [],
                tags: [GameTag::Top->value],
                name: '',
                per_page: $request->get('pageSize', 60),
                page: $page);
        } else {

            $games = GameManagerApi::getInstance()->searchGames(
                groups: [],
                categories: [],
                tags: [],
                name: $name,
                per_page: $request->get('pageSize', 60),
                page: $page);

//            $searchId = 'SearchGame:' . $name;
//
//            if ((!MUser::getAuth()?->isTestUser()) && Cache::has(Device::detect() . \app()->getLocale() . $searchId)) {
//                $games = Cache::get(Device::detect() . $searchId);
//            } else {
//                // Search games and setup pagination.
//                $games = GamesManager::searchGames($providers, $groups, $request->get('name', null));
//                Cache::put(Device::detect() . $searchId, $games, 300);
//            }
        }

//        $games->setPageSize($request->get('pageSize', 30));
//        $games->setCurrentPage($request->get('page', 0));

        if (count($games->getGames()) == 0) return null;

        $this->renderAdaptive('games.search-games', ['games' => $games, 'name' => $name]);
        return $this->sendPreparedResponse(true);
    }

    public function demoForbiddenModal(Request $request)
    {
        $id = $request->get('gameId', '');

        $game = GamesManager::getGameByCompoundId($id);

        if ($game !== null) {
            return $this->viewOrJson('common.demo_not_allowed', compact('game'));
        }
        return '';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     * @deprecated
     */
    public function balanceModeModal(Request $request)
    {
        //disabled balance mode modal
        return response('');

        $id = $request->get('providerId', '')
            . '/' . $request->get('groupId', '')
            . '/' . $request->get('gameId', '');
        $game = GamesManager::getGameByCompoundId($id, true);
        if ($game === null) {
            Log::error('Отсутствует запрашиваемая игра: ' . $id);
            return response(__('games.not_found'), 400);
        }

//        if ($game->getProviderId() === 'qt' && UserBonus::userHasCashBackCancelable(Auth::user())) {
//            return $this->viewOrJson('common.bonus_cancel_warn', compact('game'));
//        }
//        if (auth()->check() && $game->getProviderId() === 'qt') {
//            /** @var MUser $user */
//            $user = Auth::user();
//            $bonus = UserBonus::getFilthyBonus($user->id);
//            if ($bonus !== null) {
//                return $this->viewOrJson('common.bonus_not_supported', compact('game', 'bonus'));
//            }
//        }

        /** @var UserBonus $bonus */
        $bonus = auth()->check() ? Auth::user()->getActiveBonus() : null;
        if ($bonus !== null) {
            $bonus = UserBonus::convertByType($bonus);
//            if ($game->getProviderId() === 'qt') {
//                return $this->viewOrJson('common.bonus_not_supported', compact('game', 'bonus'));
//            }
            if ($bonus->forceToPlay()) {
                if (!$bonus->isGameAllowedByProvider($game->getProviderId(), $game->getId())) {

                    return $this->viewOrJson('common.bonus_not_supported', compact('game', 'bonus'));
                }
                return '';
            }
        }
//        /** @var TournamentGame $tournamentGame */
//        $tournamentGame = auth()->check() ? Auth::user()->getActiveTournamentGame() : null;

//        if (Device::detect() === 'desktop') {
            if ($bonus === null) return response('');
            if ($bonus !== null && !$bonus->isActive()) return response('');
//        }

        $this->renderAdaptive('games.balance-mode', [
            'game' => $game,
            'bonus' => $bonus,
            'tournamentGame' => null,
        ]);
        return $this->sendPreparedResponse();
    }


    public function gamesByTag(Request $request)
    {
        $tag = $request->get('tag', '');
        $games = GamesManager::getGamesByTag($tag);
        $games->setPageSize(60);
        $games->setCurrentPage($request->get('page', 0));
        $this->renderAdaptive('games.items-with-wrap', ['games' => $games, 'tag' => $tag]);

        return $this->sendPreparedResponse();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @deprecated
     */
    public function makeFavourite(Request $request)
    {
        $id = $request->post('id', null);
        if (empty($id)) {
            return response('Game ID is absent.', 400);
        }

        $game = GamesManager::getGameByCompoundId($id);
        if ($game === null) {
            return response('Game with given ID is not found.', 400);
        }

        /** @var MUser $user */
        $user = Auth::user();
        if ($user->getFavouriteGames()->containsGame($game)) {
            $user->getFavouriteGames()->removeGame($game);
        } else {
            $user->getFavouriteGames()->addGame($game);
        }
        $user->save();

        return response('OK');
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @deprecated
     */
    public function favourites()
    {
        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.favouritesTitle');
        $this->metaTags->description = __('meta.favouritesDesc');
        $this->renderAdaptive('games.favourites', [], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }
}
