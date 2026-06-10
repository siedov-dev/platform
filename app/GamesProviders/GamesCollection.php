<?php

namespace App\GamesProviders;

use ArrayAccess;
use Closure;
use Countable;
use Illuminate\Support\Facades\Log;
use Iterator;
use JsonSerializable;

/**
 * Represents collection of the games which can be iterated through using foreach operator.
 * Provides pagination functionality.
 * All necessary sorting stuff is implemented inside sort() method (call it just before using collection).
 * Returned by searching method of GamesManager class or any provider.
 *
 * Example of usage:
 *
 * $gamesCollection = GamesManager::searchGames(null, null, 'fruits');
 * $gamesCollection->sort();
 * $gamesCollection->setPageSize(10);
 * foreach ($gamesCollection as $game) {
 *     // Render single game...
 * }
 */
class GamesCollection implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    private int $_position = 0;
    private array $_gamesByGroup = [];
    private array $_gamesByProvider = [];
    private array $_allGames = [];

    private int $_pageSize = 128;
    private int $_currentPage = 0;

    public function addGame(Game $game)
    {
        if (!array_key_exists($game->getProviderId(), $this->_gamesByGroup)) {
            $this->_gamesByGroup[$game->getProviderId()] = [];
        }
        if (!array_key_exists($game->getProviderId(), $this->_gamesByProvider)) {
            $this->_gamesByProvider[$game->getProviderId()] = [];
        }

        if (!array_key_exists($game->getGroupId(), $this->_gamesByGroup[$game->getProviderId()])) {
            $this->_gamesByGroup[$game->getProviderId()][$game->getGroupId()] = [];
        }

        $this->_gamesByGroup[$game->getProviderId()][$game->getGroupId()] [$game->getId()]= $game;
        $this->_gamesByProvider[$game->getProviderId()] [$game->getId()]= $game;
        $this->_allGames []= $game;
    }


    public function empty()
    {
        $this->_gamesByProvider = [];
        $this->_gamesByGroup = [];
        $this->_allGames = [];
    }

    public function getAllProviders() : array
    {
        return array_keys($this->_gamesByProvider);
    }

    public function getAllGroups() : array
    {
        $groups = [];
        foreach ($this->_gamesByGroup as $provider => $group) {
            $groups = array_merge($groups, array_keys($group));
        }
        return $groups;
    }

    public function getGroupsByProvider(string $providerId) : array
    {
        return array_key_exists($providerId, $this->_gamesByGroup)
            ? $this->_gamesByProvider[$providerId]
            : [];
    }

    public function getGames(string $providerId, string $groupId) : array
    {
        return array_key_exists($providerId, $this->_gamesByGroup)
            && array_key_exists($groupId, $this->_gamesByGroup[$providerId])
                ? $this->_gamesByGroup[$providerId][$groupId]
                : [];
    }

    public function getAllGames() : array
    {
        return $this->_allGames;
    }

    public function getGame(string $providerId, string $groupId, string $gameId) :? Game
    {
        if (!empty($providerId)) {
            if (!array_key_exists($providerId, $this->_gamesByProvider)) return null;
            foreach ($this->_gamesByProvider[$providerId] as $game) {
                if ($game->getId() === $gameId && (empty($groupId) || $game->getGroupId() === $groupId)) {
                    return $game;
                }
            }
        } else if (array_key_exists($providerId, $this->_gamesByGroup)) {
            foreach ($this->_gamesByGroup[$providerId] as $group) {
                foreach ($group as $game) {
                    if ($game->getId() === $gameId && ($groupId === null || $game->getGroupId() === $groupId)) {
                        return $game;
                    }
                }
            }
        }
        return null;
    }


    /**
     * Removes game from the collection.
     * Note that this method is very slow, so don't use it inside the loop.
     *
     * @param Game $game
     * @see sort()
     * @see filter()
     */
    public function removeGame(Game $game)
    {
        foreach ($this->_allGames as $index => $g) {
            if ($game === $g) {
                if (array_key_exists($game->getProviderId(), $this->_gamesByGroup)) {
                    if (array_key_exists($game->getGroupId(), $this->_gamesByGroup[$game->getProviderId()])) {
                        unset($this->_gamesByGroup[$game->getProviderId()][$game->getGroupId()][$game->getId()]);
                    }
                }
                if (array_key_exists($game->getProviderId(), $this->_gamesByProvider)) {
                    unset($this->_gamesByProvider[$game->getProviderId()][$game->getId()]);
                }
                unset($this->_allGames[$index]);
                $this->_allGames = array_values($this->_allGames);
                break;
            }
        }
    }


    public function containsGame(Game $game) : bool
    {
        return $this->getGame($game->getProviderId(), $game->getGroupId(), $game->getId()) !== null;
    }


    public function setPageSize(int $pageSize)
    {
        if ($pageSize <= 0) return;
        $this->_pageSize = $pageSize > 0 && $pageSize < count($this->_allGames)
            ? $pageSize
            : count($this->_allGames);
    }

    public function getPageSize() : int {
        return $this->_pageSize;
    }

    /**
     * @param int $pageIndex Counting starts from 1
     */
    public function setCurrentPage(int $pageIndex)
    {
        $pageIndex--;
        if ($pageIndex < 0) {
            $this->_currentPage = 0;
        } else {
            $this->_currentPage = $pageIndex >= 0 && $pageIndex < $this->getTotalPages()
                ? $pageIndex
                : $this->getTotalPages() - 1;
        }
       $this->rewind();
    }

    /**
     * @return int Counting starts from 1.
     */
    public function getCurrentPage() : int
    {
        return $this->_currentPage + 1;
    }

    public function getTotalPages(): int
    {
        if ($this->_pageSize === 0) return 0;
        return ceil(count($this->_allGames)/$this->_pageSize);
    }

    public function getTotalGames() : int
    {
        return count($this->_allGames);
    }

    /**
     * Modifies collection.
     * Removes games that absent at least in one collection.
     *
     * @param GamesCollection $collection
     */
    public function intersectCollection(GamesCollection $collection): void
    {
        /** @var Game[] $games */
        $games = $this->_allGames;
        $this->empty();
        foreach ($games as $game) {
            if ($collection->getGame($game->getProviderId(), $game->getGroupId(), $game->getId()) !== null) {
                $this->addGame($game);
            }
        }
    }

    /**
     * join collection
     * @param GamesCollection $collection
     * @return void
     */
    public function addCollection(GamesCollection $collection): void
    {
        foreach ($collection->getAllGames() as $game) {
            if ($this->getGame($game->getProviderId(), $game->getGroupId(), $game->getId()) === null) {
                $this->addGame($game);
            }
        }
    }

    /**
     * Sorts games in the collection using user defined function.
     * Sorting function accepts 2 games as arguments ($gameA, $gameB)
     * and must return one of next values: -1, 0, 1 (see usort() function).
     * If sorting function is not given - default sorting algorithm will be user.
     *
     * @param Closure|null $comparingFunction
     */
    public function sort(Closure $comparingFunction = null)
    {
        if ($comparingFunction === null) {
            usort($this->_allGames, function($a, $b) {
                // If game is in top or new - move it at the start of collection.
                if ($a->isInTop()) {
                    if ($a->isNew()) return -1;
                    if ($b->isInTop() && $b->isNew()) return 1;
                    return -1;
                }
                if ($b->isInTop()) return 1;

                if ($a->isNew()) return -1;
                if ($b->isNew()) return 1;

                // Next - sort by groups appearing.
                $d = strcmp($a->getGroupId(), $b->getGroupId());
                if ($d !== 0) return $d > 0 ? 1 : -1;

                // At the end sort by game own name.
                $d = strcmp($a->getName(), $b->getName());
                if ($d === 0) return 0;
                return $d > 0 ? 1 : -1;
            });
        } else {
            usort($this->_allGames, $comparingFunction);
        }
    }

    /**
     * Filters games collection using user defined function.
     * If function returns true - game will be kept in the collection.
     * Otherwise game will be removed.
     *
     * @param Closure $closure
     */
    public function filter(Closure $closure)
    {
        $keys = [];
        foreach ($this->_allGames as $i => $game) {
            if (!$closure($game)) {
                $keys []= $i;
            }
        }
        foreach ($keys as $key) {
            $game = $this->_allGames[$key];
            unset($this->_allGames[$key]);
            if (array_key_exists($game->getProviderId(), $this->_gamesByGroup)) {
                if (array_key_exists($game->getGroupId(), $this->_gamesByGroup[$game->getProviderId()])) {
                    unset($this->_gamesByGroup[$game->getProviderId()][$game->getGroupId()][$game->getId()]);
                }
            }
            if (array_key_exists($game->getProviderId(), $this->_gamesByProvider)) {
                unset($this->_gamesByProvider[$game->getProviderId()][$game->getId()]);
            }
        }
        $this->_allGames = array_values($this->_allGames);
    }


    /**
     * Used to store collection games by compoundId
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($game) {
            /** @var Game $game */
            return json_encode($game);
        },  $this->_allGames);
    }

    public static function fromJson(string &$json): GamesCollection
    {
        $games_data = json_decode($json, true);
        $collection = new GamesCollection();
        if (!empty($games_data)) {
            foreach ($games_data as $game_data) {
//                $game = GamesManager::getGameByCompoundId($game_cid);
                $game = Game::fromJson($game_data);
                if ($game !== null) $collection->addGame($game);
            }
        }
        return $collection;
    }

    public static function fromArray(array &$games_data): GamesCollection
    {
        $collection = new GamesCollection();
        if (!empty($games_data)) {
            foreach ($games_data as $game_d) {
                $game = Game::fromArray($game_d);
                if ($game !== null) $collection->addGame($game);
            }
        }
        return $collection;
    }


    // ArrayAccess implementation.
    public function offsetExists($offset): bool { return isset($this->_allGames[$offset]); }
    public function offsetGet($offset){ return $this->_allGames[$offset]; }
    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}

    // Iterator implementation
    public function current() { return $this->_allGames[$this->_position]??null; }
    public function next(): int { return ++$this->_position; }
    public function key() { return $this->_position; }
    public function rewind() { $this->_position = $this->_currentPage * $this->_pageSize; }
    public function valid(): bool
    {
        return isset($this->_allGames[$this->_position])
            && $this->_position - ($this->_currentPage * $this->_pageSize) < $this->_pageSize;
    }

    // Countable implementation
    public function count(): int { return count($this->_allGames); }


    public function __toString() : string
    {
        return implode(',', $this->_allGames);
    }
}
