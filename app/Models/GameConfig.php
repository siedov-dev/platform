<?php

namespace App\Models;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GamesProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Сохраняет информацию о всех играх в систему.
 * Используется сервисом kgames и crm.
 * @property int $id
 * @property string $cid
 * @property string $system
 * @property string $group
 * @property string $game_id
 * @property string $name
 * @property string $img_url
 * @property string $category
 * @property string $origin_data
 * @property string $landing_category
 * @property string $landing_group
 * @property ?string $title
 * @property ?string $custom_img
 * @property int $device_type
 * @property bool $deleted
 */
class GameConfig extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    public static function getGameCategories(): array
    {
        return [
            "\u{1F3B0} " . Game::CATEGORY_SLOTS => Game::CATEGORY_SLOTS,
            "\u{26BD} " .Game::CATEGORY_SPORT => Game::CATEGORY_SPORT,
            "\u{1F3B1} " .Game::CATEGORY_LIVE => Game::CATEGORY_LIVE,
            "\u{2660} " .Game::CATEGORY_TABLE => Game::CATEGORY_TABLE,
            "\u{1F4FA} " .Game::CATEGORY_TV => Game::CATEGORY_TV,
        ];
    }

    public static function getGameSystems(): array
    {
        return [
            GamesProvider::GAME_SYSTEM_PRAGMATIC,
            GamesProvider::GAME_SYSTEM_SLOTEGRATOR,
            GamesProvider::GAME_SYSTEM_QT,
            GamesProvider::GAME_SYSTEM_VEGAS,
            GamesProvider::GAME_SYSTEM_DRUMMATIC,
            GamesProvider::GAME_SYSTEM_BET_PRODUCTION,
            GamesProvider::GAME_SYSTEM_TV_BET,
            GamesProvider::GAME_SYSTEM_SOFT_SWISS,
            GamesProvider::GAME_SYSTEM_MOBULE,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCid(): string
    {
        return $this->cid;
    }

    public function setCid(string $cid): void
    {
        $this->cid = $cid;
    }

    public function getSystem(): string
    {
        return $this->system;
    }

    public function setSystem(string $system): void
    {
        $this->system = $system;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getGameId(): string
    {
        return $this->game_id;
    }

    public function setGameId(string $game_id): void
    {
        $this->game_id = $game_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getImgUrl(): string
    {
        return $this->img_url;
    }

    public function setImgUrl(string $img_url): void
    {
        $this->img_url = $img_url;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getOriginData(): string
    {
        return $this->origin_data;
    }

    public function setOriginData(string $origin_data): void
    {
        $this->origin_data = $origin_data;
    }

    public function getLandingCategory(): string
    {
        return $this->landing_category;
    }

    public function setLandingCategory(string $landing_category): void
    {
        $this->landing_category = $landing_category;
    }

    public function getLandingGroup(): string
    {
        return $this->landing_group;
    }

    public function setLandingGroup(string $landing_group): void
    {
        $this->landing_group = $landing_group;
    }

    public function getTitle(): ?string
    {
        return empty($this->title)? $this->getName(): $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDeviceType(): DeviceType
    {
        return DeviceType::from($this->device_type);
    }

    public function setDeviceType(DeviceType $device_type): void
    {
        $this->device_type = $device_type->value;
    }

    public function getCustomImg(): ?string
    {
        return $this->custom_img;
    }

    public function setCustomImg(?string $custom_img): void
    {
        $this->custom_img = $custom_img;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(GameSegment::class);
    }
    public function chkSegment($segmentId): bool
    {
        return $this->segments->where('id', $segmentId)->count() > 0;
    }

    /**
     * Игры провайдера по группам
     * @param string $game_system
     * @param bool $deleted
     * @return array<string, array<GameConfig>>
     */
    public static function getGamesInGroups(string $game_system, bool $deleted = false): array
    {
        $q = GameConfig::query();
        $q->where('system', $game_system);
        if ($deleted) {
            $q->where('deleted', true);
        }
        $q->select('group', 'cid', 'game_id', 'name');

        $games = $q->get();

        $res = [];
        foreach ($games as $game) {
            $res[$game->group][] = $game;
        }
        return $res;
    }
}
