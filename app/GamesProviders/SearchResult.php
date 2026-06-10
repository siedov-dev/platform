<?php

namespace App\GamesProviders;

/**
 * SearchResult результат поиска игр в сервисе game_manager (kgames)
 */
class SearchResult
{
    /** @var Game[]  */
    private array $_games = [];
    private int $_games_total = 0;
    private int $_pages_total = 0;
    private int $_page = 0;
    private int $_page_size = 30;
    /** @var array group ids => games count */
    private array $_groups = [];

    public function __construct(int $games_total, int $pages_total, int $page, array $games, array $groups, int $page_size = 30)
    {
        $this->_games_total = $games_total;
        $this->_pages_total = $pages_total;
        $this->_page = $page;
        $this->_groups = $groups;
        $this->_page_size = $page_size;

        if (!empty($games)) {
            foreach ($games as $game_d) {
                $game = Game::fromArray($game_d);
                if ($game !== null) $this->_games[] = $game;
            }
        }
    }

    /**
     * get games array
     * @return array|Game[]
     */
    public function getGames(): array
    {
        return $this->_games;
    }

    /**
     * total games available
     * @return int
     */
    public function getTotalGames(): int
    {
        return $this->_games_total;
    }

    /**
     * total pages available
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->_pages_total;
    }

    /**
     * current page
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->_page;
    }

    public function getPageSize(): int
    {
        return $this->_page_size;
    }

    public function getGroups(): array
    {
        return $this->_groups;
    }
}
