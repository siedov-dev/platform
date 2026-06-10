<?php


namespace App\GamesProviders;


use App\Models\GameConfig;
use Illuminate\Support\Facades\Log;

abstract class Game implements \JsonSerializable
{
    const CATEGORY_SLOTS = 'slots';
    const CATEGORY_TABLE = 'table';
    const CATEGORY_SPORT = 'sport';
    const CATEGORY_LIVE = 'live';
    const CATEGORY_TV = 'tv';

    const LND_CATEGORY_HOT = 'hot';
    const LND_CATEGORY_POPULAR = 'top';
    const LND_CATEGORY_MEGAWAYS = 'megaways';
    const LND_CATEGORY_FS = 'fs';
    const LND_CATEGORY_NEW = 'new';
    const LND_CATEGORY_JACKPOT = 'jackpot';
    const LND_CATEGORY_CRASH = 'crash';

    protected string $_id;
    protected string $_name;
    protected string $_providerId;
    protected ?string $_groupId;
    protected ?string $_groupName;
    protected string $_imageUrl;
    protected string $_fallbackImageUrl = '/img/defaults/game.jpg';
//    protected $_originalData;
    protected $_category;
    protected $_hasDemoMode = false;
    protected $_canChooseBalance = false;
    protected $_isBonusBalanceActive = false;
//    protected $_isInTop = false;
    protected bool $_isTestMode = false;

    const TAG_NEW = 0b1;
    const TAG_HOT = 0b10;
    const TAG_JACKPOT = 0b100;
    const TAG_FS = 0b1000;
    const TAG_MW = 0b10000;
    const TAG_TOP = 0b100000;
    const TAG_CRASH = 0b1000000;

    protected int $_tag_flags = 0;

    const LND_FLAG_SLOT = 0b1;
    const LND_FLAG_TABLE = 0b10;
    const LND_FLAG_LIVE = 0b100;

    protected int $_lnd_cat_flags = 0;

//    /** @var UserBonus|null|bool */
//    private static $_activeBonus = false;

    protected DeviceType $_device_type = DeviceType::All;


    public function __construct(string $id, string $name, string $providerId,
                                string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_providerId = $providerId;
        $this->_groupId = $groupId;
        $this->_device_type = $deviceType;

//        $compoundId = $this->getCompoundId();

//        $this->_isInTop = LandingGame::isGameCategory($compoundId, 'topSlotGames')
//            || LandingGame::isGameCategory($compoundId, 'topTableGames')
//            || LandingGame::isGameCategory($compoundId, 'topLiveGames');

//        $this->_isNew = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_NEW);
//        $this->_isHot = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_HOT);
//        $this->_isJackpot = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_JACKPOT);
//        $this->_isFreeSpins = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_FS);
//        $this->_isMegaWays = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_MEGAWAYS);
//        $this->_isPopular = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_POPULAR);
//        $this->_isCrash = LandingGame::isGameCategory($compoundId, self::LND_CATEGORY_CRASH);
        $this->_category = $category;
    }

    public function setCategory(string $category)
    {
        if (isset($this->_category)) {
            Log::error('Trying to re-define game category which can be set just once.');
        } else {
            $this->_category = $category;
        }
    }

    public function isTestMode(): bool
    {
        return $this->_isTestMode;
    }

    public function setTestMode(bool $mode = true)
    {
        $this->_isTestMode = $mode;
    }

    public function isNew(): bool
    {
        return ($this->_tag_flags & self::TAG_NEW) !== 0;
    }

    public function isHot(): bool
    {
        return ($this->_tag_flags & self::TAG_HOT) !== 0;
    }

    public function isJackpot(): bool
    {
        return ($this->_tag_flags & self::TAG_JACKPOT) !== 0;
    }

    public function isFreeSpins(): bool
    {
        return ($this->_tag_flags & self::TAG_FS) !== 0;
    }

    public function isMegaWays(): bool
    {
        return ($this->_tag_flags & self::TAG_MW) !== 0;
    }

    public function isPopular(): bool
    {
        return ($this->_tag_flags & self::TAG_TOP) !== 0;
    }

    public function isCrash(): bool
    {
        return ($this->_tag_flags & self::TAG_CRASH) !== 0;
    }

    public function getTags(): int
    {
        return $this->_tag_flags;
    }

    public function setTags(int $tags): void
    {
        $this->_tag_flags = $tags;
    }

    public function setLndFlags(int $flags): void
    {
        $this->_lnd_cat_flags = $flags;
    }

    /**
     * is in landing category
     * @param string $lnd_category
     * @return bool
     */
    public function isInLndCategory(string $lnd_category): bool
    {
        switch ($lnd_category) {
            case Game::LND_CATEGORY_HOT:
                return $this->isHot();
            case Game::LND_CATEGORY_POPULAR:
                return $this->isPopular();
            case Game::LND_CATEGORY_MEGAWAYS:
                return $this->isMegaWays();
            case Game::LND_CATEGORY_FS:
                return $this->isFreeSpins();
            case Game::LND_CATEGORY_NEW:
                return $this->isNew();
            case Game::LND_CATEGORY_JACKPOT:
                return $this->isJackpot();
            case Game::LND_CATEGORY_CRASH:
                return $this->isCrash();
        }
        return false;
    }

    public function getCompoundId(): string
    {
        return $this->_providerId . '/' . $this->_groupName . '/' . $this->_id;
    }

    public function getCategory(): string
    {
        return !isset($this->_category) ? self::CATEGORY_SLOTS : $this->_category;
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getProviderId(): string
    {
        return $this->_providerId;
    }

    public function getGroupId(): string
    {
        return $this->_groupId;
    }

    public function getGroupName(): string
    {
        return $this->_groupName;
    }

    public function setGroupName(string $name): void
    {
        $this->_groupName = $name;
    }

    public function getImageUrl(): string
    {
        $img = empty($this->_imageUrl) || !file_exists(public_path($this->_imageUrl)) ? $this->_fallbackImageUrl : $this->_imageUrl;
        if (empty($img)) return "/img/defaults/game.jpg";
        return $img;
    }

    public function setImageUrl(string $img): void
    {
        $this->_imageUrl = $img;
    }

    public function setFallbackImageUrl(string $url): void
    {
        $this->_fallbackImageUrl = $url;
    }

    public function isInTop(): bool
    {
        return $this->_lnd_cat_flags != 0;
    }

    public function hasDemoMode(): bool
    {
        return $this->_hasDemoMode === true;
    }

//    public function canChooseBalance(): bool
//    {
//        return $this->_canChooseBalance === true;
//    }

//    public function isBonusBalanceActive(): bool
//    {
//        if (!$this->_isBonusBalanceActive) return false;
//        if (self::$_activeBonus === false && Auth::id()) {
//            self::$_activeBonus = UserBonus::findFirstActiveBonus(Auth::id());
//        }
//        return self::$_activeBonus !== null && self::$_activeBonus->isGameAllowedByProvider($this->_providerId, $this->_id);
//    }


    /**
     * @return bool
     * @deprecated
     */
    public function isIframeForbidden(): bool
    {
        return false;
    }

    /**
     * Returns URL for real playing.
     * @return string
     */
    abstract function getRealPlayUrl(): string;

    /**
     * Returns URL for demo playing.
     * @return string
     */
    abstract function getDemoPlayUrl(): string;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->_id,
            'name' => $this->_name,
            'providerId' => $this->_providerId,
            'groupId' => $this->_groupId,
            'groupName' => $this->_groupName,
            'fallbackImageUrl' => $this->_fallbackImageUrl,
            'category' => $this->_category,
            'deviceType' => $this->_device_type->value,
            'tags' => $this->_tag_flags,
//            'originalData' => $this->_originalData,
        ];
    }

    public static function fromJson(string $json): ?Game
    {
        $game_data = json_decode($json, true);
        $providerId = $game_data['providerId'];
        $provider = GamesManager::getProvider($providerId);
        $game = $provider->createGame(
            id: $game_data['id'],
            name: $game_data['name'],
            groupId: $game_data['groupId'],
            category: $game_data['category'] ?? 'slots',
            deviceType: isset($game_data['deviceType']) ? DeviceType::from($game_data['deviceType']) : DeviceType::All
        );
        $game->setFallbackImageUrl($game_data['fallbackImageUrl'] ?? $game_data['img_url']);
        $game->setTags($game_data['tags'] ?? 0);
        $game->setGroupName($game_data['groupName'] ?? $game_data['groupId']);
        return $game;
    }

    public static function fromArray(array &$game_data): ?Game
    {
        $provider = GamesManager::getProvider($game_data['p']);
        $game = $provider?->createGame(
            id: $game_data['id'],
            name: $game_data['nm'],
            groupId: $game_data['g'],
            category: $game_data['ct'],
            deviceType: DeviceType::from($game_data['dv']),
        );
        $game?->setFallbackImageUrl($game_data['img']);
        $game?->setTags($game_data['tgs'] ?? 0);
        $game?->setLndFlags($game_data['lf'] ?? 0);
        if ($game_data['t'] ?? false) $game?->setTestMode(true);
        if ($game_data['ci'] ?? false) $game?->setImageUrl($game_data['ci']);
        $game?->setGroupName($game_data['gn'] ?? $game_data['g']);
        return $game;
    }

    public function __toString(): string
    {
        return $this->getCompoundId();
    }

    /**
     * @return array
     * @deprecated
     */
    public function getOriginalData(): array
    {
//        return $this->_originalData;
        return json_decode(GameConfig::where('cid', $this->getCompoundId())->firts()?->origin_data ?? '{}', true);
    }

    public function getDeviceType(): DeviceType
    {
        return $this->_device_type;
    }

    public function setDeviceType(DeviceType $deviceType): void
    {
        $this->_device_type = $deviceType;
    }
}
