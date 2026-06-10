<?php


namespace App\GamesProviders;


use App\Services\Games\GameManagerApi;
use Illuminate\Support\Facades\Log;

class GamesGroup
{
    private string $_id;
    private string $_provider_id;
    private string $_name;

    private array $_games = [];
    private array $_games_by_category = [];

    private array $categories = [];
    private int $_games_total = 0;
    private int $_priority = 0;

    public function __construct(string $id, string $providerId, string $name = null, int $priority = 0)
    {
        $this->_id = $id;
        $this->_provider_id = $providerId;
        $this->_name = $name === null ? ucfirst($id) : $name;
        $this->_priority = $priority;
    }

    public function getId() : string
    {
        return $this->_id;
    }

    public function getProviderId() : string
    {
        return $this->_provider_id;
    }

    public function getName() : string
    {
        return $this->_name;
    }

    /**
     * Adds game to the group.
     * Game's group ID must be similar with this group's ID.
     * @param Game $game
     * @deprecated
     */
    public function addGame(Game $game)
    {
        if ($this->_id !== $game->getGroupId()) {
            Log::error('Trying to add game to the group with different ID (' . $game->getGroupId() . ' != ' . $this->_id . ')');
        }
        $this->_games[$game->getId()] = $game;
        $this->_games_by_category[$game->getCategory()][$game->getId()] = $game;
        $this->_games_total++;
    }

    /**
     * Returns all games of the group.
     * @return Game[]
     * @deprecated
     */
    public function getAllGames() : array
    {
        return $this->_games;
    }

    public function getAllCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function count(): int
    {
        return $this->_games_total;
    }

    public function setGamesCount(int $count): void
    {
        $this->_games_total = $count;
    }

    /**
     * Returns game with given ID or null if game with game is absent.
     * @param string $id
     * @return Game|null
     */
    public function getGameById(string $id): ?Game
    {
        return array_key_exists($id, $this->_games) ? $this->_games[$id] : null;
    }

    public function getPriority(): int
    {
        return $this->_priority;
    }

    /**
     * Returns array with games by given name.
     * @param string|null $name
     * @param string|null $categories
     * @param string|null $lnd_category
     * @return Game[]
     */
    public function searchGames(string $name = null, array $categories = null,
                                string $lnd_category = null, bool $in_disabled = false) : array
    {
        $games = GameManagerApi::getInstance()
            ->providerSearchGames(
                providerId: $this->getProviderId(),
                groups: [$this->getId()],
                categories: $categories?? [],
                tags: [$lnd_category],
                name:$name?? '',
                per_page: 30,
                page: 0,
                in_disabled: $in_disabled);
        return $games->getGames();
    }

    public function getIconUrl(): string
    {
        return "/storage/user_img/providers-circle/".str_replace(' ', '_', $this->getName()).".svg";
    }
//
//    public function jsonSerialize() : array
//    {
//        return $this->_games;
//    }
}
