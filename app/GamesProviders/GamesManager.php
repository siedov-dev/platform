<?php

namespace App\GamesProviders;


use App\GamesProviders\ApiLX\ApiLXProvider;
use App\GamesProviders\B2BSlots\B2BSlotsGamesProvider;
use App\GamesProviders\BetProduction\BetProductionProvider;
use App\GamesProviders\Mobule\MobuleGamesProvider;
use App\GamesProviders\PragmaticPlay\PragmaticPlayGamesProvider;
use App\GamesProviders\Qt\QtGamesProvider;
use App\GamesProviders\Slotegrator\SlegGamesProvider;
use App\GamesProviders\TVBet\TVBetGamesProvider;
use App\Services\Games\GameManagerApi;
use Illuminate\Support\Facades\Log;


final class GamesManager
{

    /** @var GamesProvider[] */
    private static $_providers;
    private static $_allGamesRestored = false;

    /** @var Game[] */
    private static $_allGames = [];
    /** @var GamesGroup[] */
    private static $_all_groups = null;

    private static array $stats = [];
    private static bool $stats_loaded = false;


    public static function reset()
    {
        self::$_providers = [];
        self::$_allGamesRestored = false;
        self::$_allGames = [];
        self::$stats_loaded = false;
    }

//    /** @var string[] */
//    private static $_allGroups = [];

    /**
     * Restores all providers.
     * If argument is true - all games of all providers will be fetched too.
     * @param bool $restoreAll
     */
    public static function restore(bool $restoreAll = false)
    {
        if (empty(self::$_providers)) {
            self::$_providers = [];
            $providers = [
                GamesProvider::GAME_SYSTEM_BET_PRODUCTION => BetProductionProvider::class,
                GamesProvider::GAME_SYSTEM_PRAGMATIC => PragmaticPlayGamesProvider::class,
                GamesProvider::GAME_SYSTEM_SLOTEGRATOR => SlegGamesProvider::class,
                GamesProvider::GAME_SYSTEM_QT => QtGamesProvider::class,
                GamesProvider::GAME_SYSTEM_TV_BET => TVBetGamesProvider::class,
//                GamesProvider::GAME_SYSTEM_MOBULE => MobuleGamesProvider::class,
                GamesProvider::GAME_SYSTEM_APILX => ApiLXProvider::class,
//                GamesProvider::GAME_SYSTEM_VEGAS => VegasGamesProvider::class,
//                GamesProvider::GAME_SYSTEM_SOFT_SWISS => SoftSwissGamesProvider::class
//                GamesProvider::GAME_SYSTEM_B2B_SLOTS => B2BSlotsGamesProvider::class,
            ];


            foreach ($providers as $id => $class) {
                $provider = new ($class)();
                self::$_providers[$id] = $provider;
            }
        }

        if ($restoreAll === true && !self::$_allGamesRestored) {
            foreach (self::$_providers as $provider) {
                $provider->restoreAllGames();
            }
            self::$_allGamesRestored = true;
        }

        if ($restoreAll || self::$stats_loaded === false) {
            self::$stats = GameManagerApi::getInstance()->gamesStats();
            if (!empty(self::$stats)) self::$stats_loaded = true;
        }
    }

    /**
     * Registers game in the global registry.
     * @param Game $game
     */
    public static function registerGame(Game $game)
    {
//        self::$_allGames[$game->getCompoundId()] = $game;
//        if ($game->getCategory() === Game::CATEGORY_SLOTS) {
//            self::$_totalSlotGames++;
//        } else if ($game->getCategory() === Game::CATEGORY_LIVE) {
//            self::$_totalLiveGames++;
//        } else if ($game->getCategory() === Game::CATEGORY_TABLE) {
//            self::$_totalTableGames++;
//        }
//        if (!in_array($game->getGroupId(), self::$_allGroups)) {
//            self::$_allGroups[] = $game->getGroupId();
//        }

//        if ($game->isNew()) self::$_totalNewGames++;
//        if ($game->isHot()) self::$_totalHotGames++;
//        if ($game->isPopular()) self::$_totalPopularGames++;
//        if ($game->isJackpot()) self::$_totalJackpotGames++;
//        if ($game->isFreeSpins()) self::$_totalFreeSpinsGames++;
//        if ($game->isMegaWays()) self::$_totalMegaWaysGames++;
//        if ($game->isCrash()) self::$_totalCrashGames++;
    }

    public static function getTotalNewGames(): int
    {
        return self::$stats['Tags']['new']?? 0;
    }

    public static function getTotalHotGames(): int
    {
        return self::$stats['Tags']['hot']?? 0;
    }

    public static function getTotalJackpotGames(): int
    {
        return self::$stats['Tags']['jackpot']?? 0;
    }

    public static function getTotalPopularGames(): int
    {
        return self::$stats['Tags']['top']?? 0;
    }

    public static function getTotalMegaWaysGames(): int
    {
        return self::$stats['Tags']['megaways']?? 0;
    }

    public static function getTotalFreeSpinsGames(): int
    {
        return self::$stats['Tags']['fs']?? 0;
    }

    public static function getTotalCrashGames(): int
    {
        return self::$stats['Tags']['crash']?? 0;
    }

    /**
     * Returns quantity of all games.
     * @return int
     */
    public static function getTotalGames(): int
    {
        return self::$stats['TotalGames']?? 0;
    }

    /**
     * Returns quantity of all slot games.
     * @return int
     */
    public static function getTotalSlotGames(): int
    {
        return self::$stats['Categories']['slots']?? 0;
    }

    /**
     * Returns quantity of all live games.
     * @return int
     */
    public static function getTotalLiveGames(): int
    {
        return self::$stats['Categories']['live']?? 0;
    }

    /**
     * Returns quantity of all table games.
     * @return int
     */
    public static function getTotalTableGames(): int
    {
        return self::$stats['Categories']['table']?? 0;
    }

    public static function getLandingTableGames(): int
    {
        return self::$stats['LandingGroups']['topTableGames']?? 0;
    }

    public static function getLandingLiveGames(): int
    {
        return self::$stats['LandingGroups']['topLiveGames']?? 0;
    }

    public static function getLandingSlotGames(): int
    {
        return self::$stats['LandingGroups']['topSlotGames']?? 0;
    }

    /**
     * Returns game by given compound ID.
     * Compound ID can be composed in next way: providerId/GroupId/GameId
     * @param string $id
     * @param bool $in_disabled search in disabled games
     * @return Game|null
     */
    public static function getGameByCompoundId(string $id, bool $in_disabled = false): ?Game
    {
        $api = GameManagerApi::getInstance();
        return $api->gameByCid($id, $in_disabled);
    }

    /**
     * Returns all providers (key is provider ID).
     * @return GamesProvider[]
     */
    public static function getAllProviders(): array
    {
        self::restore();
        return self::$_providers;
    }

    /**
     * Returns provider with given ID (qt, vegas, drummatic, etc.)
     * @param string $providerId
     * @return GamesProvider|null
     */
    public static function getProvider(string $providerId): ?GamesProvider
    {
        self::restore();
        return array_key_exists($providerId, self::$_providers)
            ? self::$_providers[$providerId]
            : null;
    }

    /**
     * @param array|null $providerIds
     * @param array|null $groupIds
     * @param string|null $name
     * @param string[]|null $categories
     * @param string|null $lnd_category
     * @return GamesCollection
     * @deprecated
     * @see GameManagerApi@searchGames
     * Searches games by given groups and name.
     * If searching must be done only by name - pass null as first and second parameters.
     *
     */
    public static function searchGames(array $providerIds = null, array $groupIds = null, string $name = null,
                                       array $categories = null, string $lnd_category = null,
                                       bool $in_hidden = false): GamesCollection
    {
        if (empty(self::$_providers)) self::restore();
        $collection = new GamesCollection();
        foreach (self::$_providers as $provider) {
            if ($providerIds === null || count($providerIds) === 0 || in_array($provider->getId(), $providerIds)) {
                $provider->searchGames($groupIds, $name, $categories, $collection, $lnd_category, $in_hidden);
            }
        }
        return $collection;
    }

    /**
     * get games collection by tag name
     * ordered identical to database
     * @param $tag
     * @return GamesCollection
     */
    public static function getGamesByTag($tag): GamesCollection
    {
        $games = GameManagerApi::getInstance()->gamesByTag($tag);
        if (!empty($games)) {
            return GamesCollection::fromArray($games);
        }
        Log::error('No games by tag '.$tag);
        return new GamesCollection();
    }

    public static function gamesByLandingGroup(string $l_category): GamesCollection
    {
        $games = GameManagerApi::getInstance()->gamesByLandingGroup($l_category);
        if (!empty($games)) {
            return GamesCollection::fromArray($games);
        }
        Log::error('No games by landing group '.$l_category);
        return new GamesCollection();
    }

    /**
     * return array of all groups
     * @return array
     */
    public static function getAllGroups(bool $sort = true): array
    {
        if (self::$_all_groups === null) {
            $groupsData = GameManagerApi::getInstance()->allGroups();
            foreach ($groupsData as $groupData) {
                $group = new GamesGroup($groupData['name'], "", $groupData['name'], $groupData['pr']?? 0);
                $group->setGamesCount($groupData['games_count']);
                $group->setCategories($groupData['categories']);
                self::$_all_groups[] = $group;
            }
//            $providers = GamesManager::getAllProviders();
//            self::$_all_groups = [];
//            foreach ($providers as $provider) {
//                foreach ($provider->getAllGroups() as $group) {
//                    self::$_all_groups[] = $group;
//                }
//            }
//
            if ($sort) {
                // Sort providers groups.
                usort(self::$_all_groups, function ($a, $b) {
                    if ($a->getPriority() !== $b->getPriority()) {
                        return $b->getPriority() - $a->getPriority();
                    };
                    return strcmp($a->getName(), $b->getName());
                });
            }

        }
        return self::$_all_groups;
    }
}
