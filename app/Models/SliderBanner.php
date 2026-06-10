<?php

namespace App\Models;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * Баннеры карусели лендинга.
 * @property int $id
 * @property string $locale
 * @property int $priority
 * @property string $img
 * @property string $url
 * @property int $type
 * @property bool $time_limited
 * @property ?Carbon $start_time
 * @property ?Carbon $end_time
 */
class SliderBanner extends Model
{
    use HasFactory;

    const TYPE_DEFAULT = 0;
    const TYPE_WELCOME_OFFER = 1;
    const TYPE_EXTERNAL_LINK = 2;


    public $timestamps = false;

    protected $dates = [
        'start_time',
        'end_time',
    ];

    /**
     * get main slider banners
     * @param MUser|null $user is authenticated
     * @param string|null $locale locale
     * @return SliderBanner[]
     */
    public static function getBanners(?MUser $user = null, ?string $locale = null): Collection
    {
        if ($locale === null) $locale = App::getLocale();
        /** @var SliderBanner[] $banners */
        $banners = SliderBanner::where(function ($q) use ($locale) {
            $q->where('locale', $locale);
            $q->orWhere('locale', 'all');
        })
            ->orderBy('priority')
            ->get();
        foreach ($banners as $k => $banner) {
            if ($banner->getType() === self::TYPE_WELCOME_OFFER) {
                if ($user !== null) {
                    $welcome_pack = \App\Models\Bonuses\UserBonus::where('user_id', $user->getId())->first();
                    if (!$welcome_pack?->isCancelable()) {
                        $banners->forget($k);
                    }
                }
            }
            if ($banner->isTimeLimited()) {
                $now = now();
                if ($banner->getStartTime() > $now || $banner->getEndTime() < $now) {
                    $banners->forget($k);
                }
            }
        }
        return $banners->values();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function isTimeLimited(): bool
    {
        return $this->time_limited;
    }

    public function setTimeLimited(bool $time_limited): void
    {
        $this->time_limited = $time_limited;
    }

    public function getStartTime(): ?Carbon
    {
        return $this->start_time;
    }

    public function setStartTime(?Carbon $start_time): void
    {
        $this->start_time = $start_time;
    }

    public function getEndTime(): ?Carbon
    {
        return $this->end_time;
    }

    public function setEndTime(?Carbon $end_time): void
    {
        $this->end_time = $end_time;
    }

    public function getCrmTypeName(): string
    {
        return match ($this->type) {
            self::TYPE_DEFAULT => 'Обычный',
            self::TYPE_EXTERNAL_LINK => 'Внешняя ссылка',
            self::TYPE_WELCOME_OFFER => 'Велкам бонус',
        };
    }
}
