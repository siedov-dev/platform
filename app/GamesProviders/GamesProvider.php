<?php

namespace App\GamesProviders;

use App\Services\Games\GameManagerApi;
use App\Helpers\Device;
use App\Helpers\Measure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

abstract class GamesProvider
{
    const GAME_SYSTEM_DRUMMATIC = 'drummatic';
    const GAME_SYSTEM_VEGAS = 'vegas';
    const GAME_SYSTEM_QT = 'qt';
    const GAME_SYSTEM_SLOTEGRATOR = 'sleg';
    const GAME_SYSTEM_PRAGMATIC = 'pragplay';
    const GAME_SYSTEM_TV_BET = 'tv-bet';
    const GAME_SYSTEM_BET_PRODUCTION = 'bet-production';
    const GAME_SYSTEM_SOFT_SWISS = 'soft-swiss';
    const GAME_SYSTEM_B2B_SLOTS = 'b2bslots';
    const GAME_SYSTEM_MOBULE = 'mobule';
    const GAME_SYSTEM_APILX = 'apilx';

    protected $_id;

    /** @var GamesGroup[] */
    protected $_gamesByGroup;
    /** @var Game[] */
    protected array $_gamesHidden;

    protected $_api;

    protected $_groupsNames;
    private int $_totalGamesCount = 0;
    private bool $_isRestored = false;
    protected bool $_isEnabled = true;
    protected bool $_testGamesEnabled = false;
    protected bool $_isTestMode = false;
    protected bool $_isHidden = false;

    public function __construct($conf = [], bool $test = false)
    {
        $this->_groupsNames = $conf['groupsNames'] ?? [];
    }

    public function restoreAllGames(bool $useCache = true)
    {
        Measure::logNow($this->getId() . ' restoreAllGames start');
        if ($this->_isRestored) return;
//        $data = null;
        $this->_gamesByGroup = [];
        $this->_gamesHidden = [];

        if (!$this->isEnabled()) {
            $this->_isRestored = true;
            return;
        }

//        if ($useCache) {
            $groups_data = GameManagerApi::getInstance()->providerLoadGroups($this->getId());
            foreach ($groups_data as $gr) {
                $groupName = $this->_groupsNames[$gr['name']] ?? null;
                $group = new GamesGroup($gr['name'], $this->getId(), $groupName, $gr['pr']?? 0);
                $group->setGamesCount($gr['games_count']);
                $group->setCategories($gr['categories']);
                $this->_gamesByGroup[$gr['name']] = $group;
            }
            $this->_isRestored = true;
            Measure::logNow($this->getId() . ' restoreAllGames end');
//            return;

//        }

//        if ($data === null) {
//            $this->fetchAllGames();
//            $this->storeAllGames();
//        } else {
//            $groups = json_decode($data, true);
//            foreach ($groups as $games) {
//                foreach ($games as $g) {
//                    $game = $this->createGame($g['id'], $g['name'], $g['groupId'],
//                        $g['category'] ?? 'slots', isset($g['deviceType']) ? DeviceType::from($g['deviceType']) : DeviceType::All);
//                    $game->setFallbackImageUrl($g['fallbackImageUrl']);
//                    $this->addGame($game);
//                }
//            }
//        }
        $this->_isRestored = true;
        Measure::logNow($this->getId() . ' restoreAllGames end');
    }


    protected function storeAllGames()
    {
        // Don't replace this for cache with ttl. Update must be done using crontab.
        if (isset($this->_gamesByGroup)) {
            Cache::forever(Device::detect() . 'games:' . App::getLocale() . ':' . $this->_id, json_encode($this->_gamesByGroup));
        }
    }

    abstract public function createGame(string $id, string $name, string $groupId,
                                        string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game;

    /**
     * Method used to retrieve all games via provider API.
     * Don't use cache here.
     * This method will be called on first games searching request.
     * Please use addGame method for adding the game.
     * @see addGame
     */
    abstract protected function fetchAllGames();

    /**
     * Returns games provider ID (vegas, qt, etc.)
     * @return string
     */
    public function getId(): string
    {
        return $this->_id;
    }

    public function isEnabled(): bool
    {
        return $this->_isEnabled;
    }

    /**
     * Returns all games groups.
     * @return GamesGroup[]
     */
    public function getAllGroups(): array
    {
        if (!isset($this->_gamesByGroup)) {
            $this->restoreAllGames();
        }
        return $this->_gamesByGroup;
    }

    /**
     * are system's games in test mode active
     * @return bool
     */
    public function testGamesEnabled(): bool
    {
        return $this->_testGamesEnabled;
    }

    /**
     * is system's games hidden
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->_isHidden;
    }

    /**
     * system is in test mode
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->_isTestMode;
    }

    /**
     * @param Game $game
     * @deprecated
     * Adds game to the corresponding arrays.
     */
    protected function addGame(Game $game)
    {
        if (!isset($this->_gamesByGroup)) $this->_gamesByGroup = [];

        $group = $this->getGamesGroup($game->getGroupId());
        if ($group === null) {
            $groupName = $this->_groupsNames[$game->getGroupId()] ?? null;
            $group = new GamesGroup($game->getGroupId(), $this->getId(), $groupName);
            $this->_gamesByGroup[$game->getGroupId()] = $group;
        }

//        $group->addGame($game);
//        GamesManager::registerGame($game);
        $this->_totalGamesCount++;
    }

    /**
     * Returns games group with given ID.
     * @param string $groupId
     * @return GamesGroup|null
     */
    public function getGamesGroup(string $groupId): ?GamesGroup
    {
        if (!isset($this->_gamesByGroup)) $this->restoreAllGames();
        return array_key_exists($groupId, $this->_gamesByGroup)
            ? $this->_gamesByGroup[$groupId]
            : null;
    }

    /**
     * Returns quantity of all provider games.
     * @return int
     */
    public function getTotalGamesCount(): int
    {
        return $this->_totalGamesCount;
    }

    /**
     * @param string[] $groupIds
     * @param string|null $name
     * @param string[]|null $categories
     * @param GamesCollection|null $collection
     * @param string|null $lnd_category
     * @param bool $in_hidden
     * @return GamesCollection
     */
    public function searchGames(array           $groupIds = null, string $name = null, array $categories = null,
                                GamesCollection $collection = null, string $lnd_category = null,
                                bool            $in_hidden = false): GamesCollection
    {
//        if (!isset($this->_gamesByGroup)) {
//            $this->restoreAllGames();
//        }

        if ($collection === null) $collection = new GamesCollection();

        foreach ($this->_gamesByGroup as $group) {
            if ($groupIds === null || count($groupIds) === 0 || in_array($group->getId(), $groupIds)) {
                $games = $group->searchGames($name, $categories, $lnd_category, $in_hidden);
                foreach ($games as $game) {
                    $collection->addGame($game);
                }
            }
        }

        return $collection;
    }

    /**
     * get all games of the group
     * @param string $groupId
     * @return GamesCollection
     */
    public function getGroupGames(string $groupId): GamesCollection
    {
        return GameManagerApi::getInstance()->providerGetGroupGames($this->getId(), $groupId) ?? new GamesCollection();
    }

    /**
     * Returns game with given ID.
     * @param string $groupId
     * @param string $gameId
     * @param bool $in_hidden search in disabled games
     * @return Game|null
     */
    public function getGame(string $groupId, string $gameId, bool $in_hidden = false): ?Game
    {
        if ($groupId === '') {
            return GameManagerApi::getInstance()->providerGetGame($this->getId(), $gameId, $in_hidden);
        }

        return GameManagerApi::getInstance()->providerGetGroupGame($this->getId(), $groupId, $gameId, $in_hidden);
    }

    /**
     * Returns API for the given games provider.
     * @return GameProviderApi
     */
    abstract public function getApi(): GameProviderApi;

    /**
     * update games cache
     * @return mixed
     */
    abstract public function cacheGames();

    /**
     * cache group new names
     * @return void
     */
    public function cacheGroupNames(): void
    {
        $names = ProviderNames::where('system', $this->getId())->pluck('name', 'group')->toArray();
        if (!empty($names)) Redis::hMSet('groupNames:' . $this->getId(), $names);
    }
}
