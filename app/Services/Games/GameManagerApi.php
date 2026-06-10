<?php

namespace App\Services\Games;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GameTag;
use App\GamesProviders\SearchResult;
use App\Helpers\Device;
use App\MUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GameManagerApi
{
    private static ?GameManagerApi $_api = null;
    private string $host = "localhost";
    private string $port = "8095";
    private bool $test_mode = false;

    private string $currency = "";
    private string $device = "";

    public function __construct(string $device, string $currency = "", bool $test_mode = false)
    {
        $this->device = $device;
        $this->currency = $currency;
        if ($test_mode) {
            $this->test_mode = $test_mode;
            $this->currency = "test";
        }
        $this->host = config('services.gameManagerHost');
        $this->port = config('services.gameManagerPort');
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    public function isTestMode(): bool
    {
        return $this->test_mode;
    }

    public function setTestMode(bool $test_mode): void
    {
        $this->test_mode = $test_mode;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function setDevice(string $device): void
    {
        $this->device = $device;
    }

    public static function getInstance(?string $device = null, ?string $currency = null, ?bool $test_mode = null): GameManagerApi
    {
        if (self::$_api === null) {
            if ($device === null) $device = Device::detect();
            if ($currency === null) $currency = MUser::getAuth()?->getCurrencyName()?? "";
            if ($test_mode === null) $test_mode = Auth::check() && (MUser::getAuth()?->isTestUser()?? false);
            self::$_api = new GameManagerApi(
                device: $device,
                currency: $currency,
                test_mode: $test_mode,
            );
        }
        return self::$_api;
    }

    public function status(): ?string
    {
        $resp = Http::asJson()->get($this->host . ":" . $this->port . "/");
        return $resp->body();
    }

    public function reload(): string
    {
        $resp = Http::asJson()->get($this->host . ":" . $this->port . "/reload");
        return $resp->body();
    }

    public function gameByCid(string $cid, bool $in_disabled = false): ?Game
    {
        $url = $this->host . ":" . $this->port . "/byCid/" . $this->device . "/" . $this->currency . "/" . urlencode($cid);
        $params = [];
        if ($in_disabled) {
            $params['in_disabled'] = $in_disabled;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $resp = Http::asJson()->acceptJson()->get($url);
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            $game = Game::fromArray($data["Data"]);
            return $game;
        }
        return null;
    }

    public function newGamesHash(): string
    {
        $url = $this->host . ":" . $this->port . "/games/newGamesHash/" . $this->device . "/" . $this->currency;
        $resp = Http::asJson()->acceptJson()->get($url);
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data['games_hash'];
        }
        return "";
    }

    public function providerGetGame(string $providerId, string $id, bool $in_disabled = false): ?Game
    {
        $providerId = urlencode($providerId);
        $id = urlencode($id);

        $url = "$this->host:$this->port/provider/$this->device/$this->currency/$providerId/getGame/$id";
        $params = [];
        if ($in_disabled) {
            $params['in_disabled'] = $in_disabled;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $resp = Http::asJson()->acceptJson()->get($url);
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            $game = Game::fromArray($data["Data"]);
            return $game;
        }
        return null;
    }

    public function providerGetGroupGames(string $providerId, string $groupId): ?GamesCollection
    {
        $providerId = urlencode($providerId);
        $groupId = urlencode($groupId);
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/provider/$this->device/$this->currency/$providerId/groupGames/$groupId");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return GamesCollection::fromArray($data["Data"]);
        }
        return null;
    }

    public function providerGetGroupGame(string $providerId, string $groupId, string $id, bool $in_disabled = false): ?Game
    {
        $providerId = urlencode($providerId);
        $id = urlencode($id);
        $url = "$this->host:$this->port/provider/$this->device/$this->currency/$providerId/getGroupGame/$groupId/$id";
        $params = [];
        if ($in_disabled) {
            $params['in_disabled'] = $in_disabled;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $resp = Http::asJson()->acceptJson()->get($url);
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            $game = Game::fromArray($data["Data"]);
            return $game;
        }
        return null;
    }

    public function providerAllGamesArray(string $providerId): array
    {
        $providerId = urlencode($providerId);
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/provider/$this->device/$this->currency/$providerId/allGames");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Data"];
        }
        return [];
    }

    /**
     * Возвращает информацию об игровых группах провайдера(системы)
     * @param string $providerId
     * @return array|mixed
     */
    public function providerLoadGroups(string $providerId): array
    {
        $providerId = urlencode($providerId);
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/provider/$this->device/$this->currency/$providerId/getGroups");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Data"]?? [];
        }
        return [];
    }

    /**
     * Возвращает информацию о всех игровых группах
     * @return array|mixed
     */
    public function allGroups(): array
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/groups/$this->device/$this->currency");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Data"]?? [];
        }
        Log::error("Games Manager error get all groups:" . $data['Error'] ?? "unknown");
        return [];
    }

    public function gamesByTag(string $tag): array
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/byTag/$this->device/$this->currency/$tag");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Data"];
        }
        return [];
    }

    public function gamesByLandingGroup(string $group): array
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/byLndGroup/$this->device/$this->currency/$group");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Data"];
        }
        return [];
    }

    /**
     * search games
     * @param array $groups search groups
     * @param array $categories search categories (slot, table....)
     * @param array $tags game tags (top, hot...)
     * @param string $name game name like
     * @param int $per_page games per page
     * @param int $page current page, 0 - get all games
     * @param bool $in_disabled search in disabled games
     * @param int $for_bonus_type search in all allowed for bonus type games
     * @return SearchResult
     */
    public function searchGames(array  $groups = [],
                                array  $categories = [],
                                array  $tags = [],
                                string $name = '',
                                int    $per_page = 30,
                                int    $page = 1,
                                bool   $in_disabled = false,
                                int    $for_bonus_type = 0,
    ): SearchResult
    {
        $groups = urlencode(join(',', $groups));
        $name = urlencode($name);

        $categ_real = [];
        //if "category" is tag
        foreach ($categories as $cat) {
            if (GameTag::tryFrom($cat)) {
                $tags[] = $cat;
            } else {
                $categ_real[] = $cat;
            }
        }

        $categories = urlencode(join(',', $categ_real));

        $tags = urlencode(join(',', $tags));
        $url = "$this->host:$this->port/search/$this->device/$this->currency/$groups/$categories/$tags/$name/$per_page/$page";
        $get_params = [];
        if ($in_disabled) $get_params['in_disabled'] = 1;
        if ($for_bonus_type > 0) $get_params['for_bonus_type'] = $for_bonus_type;
        if (!empty($get_params)) $url .= '?'.http_build_query($get_params);

        $resp = Http::asJson()
            ->acceptJson()
            ->get($url);

        $data = json_decode($resp->body(), true);

        if (empty($data["Status"])) {
            Log::error("Games Manager network error:" . var_export($resp->body(), true));
            return new SearchResult(0, 0, 0, [], []);
        }
        if ($data["Status"] === 200) {
            return new SearchResult(
                games_total: $data['GamesTotal'],
                pages_total: $data['PagesTotal'],
                page: $data['Page'],
                games: $data["Data"] ?? [],
                groups: $data["Groups"] ?? [],
                page_size: $per_page);
        } else {
            Log::error("Games Manager error:" . $data['Error'] ?? "unknown");
        }
        return new SearchResult(0, 0, 0, [], []);
    }

    /**
     * search games
     * @param array $groups search groups
     * @param array $categories search categories (slot, table....)
     * @param array $tags game tags (top, hot...)
     * @param string $name game name like
     * @param int $per_page games per page
     * @param int $page current page, 0 - get all games
     * @param bool $in_disabled search in disabled games
     * @param int $for_bonus_type search in all allowed for bonus type games
     * @return SearchResult
     */
    public function searchTournamentGames(array  $groups = [],
                                array  $categories = [],
                                array  $tags = [],
                                string $name = '',
                                int    $per_page = 30,
                                int    $page = 1,
                                bool   $in_disabled = false,
                                int    $for_bonus_type = 0,
    ): SearchResult
    {
        $groups = urlencode(join(',', $groups));
        $name = urlencode($name);

        $categ_real = [];
        //if "category" is tag
        foreach ($categories as $cat) {
            if (GameTag::tryFrom($cat)) {
                $tags[] = $cat;
            } else {
                $categ_real[] = $cat;
            }
        }

        $categories = urlencode(join(',', $categ_real));

        $tags = urlencode(join(',', $tags));
        $url = "$this->host:$this->port/tournamentgames/search/$this->device/$this->currency/$groups/$categories/$tags/$name/$per_page/$page";
        $get_params = [];
        if ($in_disabled) $get_params['in_disabled'] = 1;
        if ($for_bonus_type > 0) $get_params['for_bonus_type'] = $for_bonus_type;
        if (!empty($get_params)) $url .= '?'.http_build_query($get_params);

        $resp = Http::asJson()
            ->acceptJson()
            ->get($url);

        $data = json_decode($resp->body(), true);

        if (empty($data["Status"])) {
            Log::error("Games Manager network error:" . var_export($resp->body(), true));
            return new SearchResult(0, 0, 0, [], []);
        }
        if ($data["Status"] === 200) {
            return new SearchResult(
                games_total: $data['GamesTotal'],
                pages_total: $data['PagesTotal'],
                page: $data['Page'],
                games: $data["Data"] ?? [],
                groups: $data["Groups"] ?? [],
                page_size: $per_page);
        } else {
            Log::error("Games Manager error:" . $data['Error'] ?? "unknown");
        }
        return new SearchResult(0, 0, 0, [], []);
    }

    /**
     * search games by provider
     * @param string $providerId provider id (required)
     * @param array $groups search groups
     * @param array $categories search categories (slot, table....)
     * @param array $tags game tags (top, hot...)
     * @param string $name game name like
     * @param int $per_page games per page
     * @param int $page current page, 0 - get all games
     * @return SearchResult
     */
    public function providerSearchGames(string $providerId, array $groups = [], array $categories = [],
                                        array  $tags = [], string $name = '',
                                        int    $per_page = 30, int $page = 1, bool $in_disabled = false): SearchResult
    {
        $providerId = urlencode($providerId);
        $groups = urlencode(join(',', $groups));
        $name = urlencode($name);

        $categ_real = [];
        //if "category" is tag
        foreach ($categories as $cat) {
            if (GameTag::tryFrom($cat)) {
                $tags[] = $cat;
            } else {
                $categ_real[] = $cat;
            }
        }

        $categories = urlencode(join(',', $categ_real));

        $tags = urlencode(join(',', $tags));

        $url = "$this->host:$this->port/provider/$this->device/$this->currency/$providerId/searchGames/$groups/$categories/$tags/$name/$per_page/$page";
        $params = [];
        if ($in_disabled) {
            $params['in_disabled'] = $in_disabled;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $resp = Http::asJson()->acceptJson()->get($url);

        $data = json_decode($resp->body(), true);

        if (empty($data["Status"])) {
            Log::error("Games Manager network error:" . var_export($resp->body(), true));
            return new SearchResult(0, 0, 0, [], []);
        }
        if ($data["Status"] === 200) {
            return new SearchResult(
                games_total: $data['GamesTotal'],
                pages_total: $data['PagesTotal'],
                page: $data['Page'],
                games: $data["Data"] ?? [],
                groups: $data["Groups"] ?? [],
                page_size: $per_page);
        } else {
            Log::error("Games Manager error:" . $data['Error'] ?? "unknown");
        }
        return new SearchResult(0, 0, 0, [], []);
    }

    public function bonusGames(int    $bonus_id,
                               array  $groups = [],
                               array  $categories = [],
                               array  $tags = [],
                               string $name = '',
                               int    $per_page = 30,
                               int    $page = 1,
    ): SearchResult
    {
        $groups = urlencode(join(',', $groups));
        $name = urlencode($name);

        $categ_real = [];
        //if "category" is tag
        foreach ($categories as $cat) {
            if (GameTag::tryFrom($cat)) {
                $tags[] = $cat;
            } else {
                $categ_real[] = $cat;
            }
        }

        $categories = urlencode(join(',', $categ_real));

        $tags = urlencode(join(',', $tags));

        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/bonusGames/$this->device/$this->currency/$bonus_id/$groups/$categories/$tags/$name/$per_page/$page");

        $data = json_decode($resp->body(), true);

        if (empty($data["Status"])) {
            Log::error("Games Manager network error:" . var_export($resp->body(), true));
            return new SearchResult(0, 0, 0, [], []);
        }
        if ($data["Status"] === 200) {
            return new SearchResult(
                games_total: $data['GamesTotal'],
                pages_total: $data['PagesTotal'],
                page: $data['Page'],
                games: $data["Data"] ?? [],
                groups: $data["Groups"] ?? [],
                page_size: $per_page);
        } else {
            Log::error("Games Manager error:" . $data['Error'] ?? "unknown");
        }
        return new SearchResult(0, 0, 0, [], []);
    }

    public function gamesStats(): array
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/games/stats/$this->device/$this->currency");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["Stats"];
        }
        return [];
    }

    public function systemGroupNamesOverloads(string $system)
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/providerNames/$system");;
        $data = json_decode($resp->body(), true);
        if (!empty($data) && empty($data["Error"])) {
            return $data;
        }
        return [];
    }

    public function getGameSegmentsGames(int $segment_id,
                                         array  $groups = [],
                                         array  $categories = [],
                                         array  $tags = [],
                                         string $name = '',
                                         int    $per_page = 30,
                                         int    $page = 1,
                                         bool   $in_disabled = false,
                                         int    $for_bonus_type = 0,
    ): SearchResult
    {
        $groups = urlencode(join(',', $groups));
        $name = urlencode($name);

        $categ_real = [];
        //if "category" is tag
        foreach ($categories as $cat) {
            if (GameTag::tryFrom($cat)) {
                $tags[] = $cat;
            } else {
                $categ_real[] = $cat;
            }
        }

        $categories = urlencode(join(',', $categ_real));

        $tags = urlencode(join(',', $tags));
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/games/segment/$this->device/$this->currency/$segment_id/$groups/$categories/$tags/$name/$per_page/$page");

        $data = json_decode($resp->body(), true);

        if (empty($data["Status"])) {
            Log::error("Games Manager network error:" . var_export($resp->body(), true));
            return new SearchResult(0, 0, 0, [], []);
        }
        if ($data["Status"] === 200) {
            return new SearchResult(
                games_total: $data['GamesTotal'],
                pages_total: $data['PagesTotal'],
                page: $data['Page'],
                games: $data["Data"] ?? [],
                groups: $data["Groups"] ?? [],
                page_size: $per_page);
        } else {
            Log::error("Games Manager error:" . $data['Error'] ?? "unknown");
        }
        return new SearchResult(0, 0, 0, [], []);
    }

    public function tournamentGamesHash(): string
    {
        $resp = Http::asJson()
            ->acceptJson()
            ->get("$this->host:$this->port/tournamentgames/hash");
        $data = json_decode($resp->body(), true);
        if (!empty($data) && $data["Status"] === 200) {
            return $data["games_hash"];
        }
        return "";
    }

}
