<?php

namespace App\Models\Promos;

use App\Helpers\UI;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property ?int $id
 * @property PromoItemType $type
 * @property ?string $action_url
 * @property ?string $img
 * @property ?string $control_texts
 * @property ?string $descriptions
 * @property bool $has_description
 * @property bool $show_timer
 * @property ?Carbon $start_time
 * @property ?Carbon $end_time
 * @property ?Carbon $show_at
 * @property bool $active
 * @property int $priority
 */
class PromoItem extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => PromoItemType::class,
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time',
        'show_at',
    ];

    /**
     * @param MUser|null $user
     * @return Collection|PromoItem[]
     */
    public static function getAvailable(?MUser $user): Collection
    {
        // activate all disabled promos that should be shown at the moment
        PromoItem::where('active', false)
            ->where('type', PromoItemType::SpecificDate->value)
            ->whereNotNull('show_at')
            ->where('show_at', '<=', now()->format('Y-m-d H:i:s'))
            ->update(['active' => true]);


        $q = PromoItem::query();
        $q->orderBy('priority');
        // only active promos if not test user
        if (!$user?->isTestUser()) {
            $q->where('active', true);
        }
        $data = $q->get();
        $promos = new Collection();
        /** @var PromoItem $promo */
        foreach ($data as $promo) {

            // skip welcome bonus promo for users already played it
            if ($promo->getType() === PromoItemType::WelcomeBonus && ($user !== null)) {
                $welcome_pack = \App\Models\Bonuses\UserBonus::where('user_id', $user->getId())->first();
                if (!$welcome_pack?->isCancelable()) continue;
            }

            //disable expired promos
            if ($promo->getType() === PromoItemType::SpecificDate && $promo->getEndTime() < now())  {
                $promo->setActive(false);
                $promo->save();
                continue;
            }
            //add to result collection
            $promos->add($promo);
        }
        return $promos;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getType(): PromoItemType
    {
        return $this->type;
    }

    public function setType(PromoItemType $type): void
    {
        $this->type = $type;
    }

    public function getActionUrl(): ?string
    {
        return $this->action_url;
    }

    public function setActionUrl(?string $action_url): void
    {
        $this->action_url = $action_url;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;
    }

    public function getControlTexts(): ?string
    {
        return $this->control_texts;
    }

    public function setControlTexts(?string $control_texts): void
    {
        $this->control_texts = $control_texts;
    }

    public function getDescriptions(): ?string
    {
        return $this->descriptions;
    }

    public function setDescriptions(?string $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    public function hasDescription(): bool
    {
        return $this->has_description;
    }

    public function setHasDescription(bool $has_description): void
    {
        $this->has_description = $has_description;
    }

    public function shouldShowTimer(): bool
    {
        return $this->show_timer;
    }

    public function setShowTimer(bool $show_timer): void
    {
        $this->show_timer = $show_timer;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getShowAt(): ?Carbon
    {
        return $this->show_at;
    }

    public function setShowAt(?Carbon $show_at): void
    {
        $this->show_at = $show_at;
    }

    public function getTitle(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getControlTexts(), true);
        return $texts[$locale]['title']?? $texts['en']['title']?? null;
    }

    public function getSlogan(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getControlTexts(), true);
        return $texts[$locale]['slogan']?? $texts['en']['slogan']?? null;
    }

    public function getBtnText(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getControlTexts(), true);
        return $texts[$locale]['button']?? $texts['en']['button']?? null;
    }

    public function getDescriptionBtnText(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getControlTexts(), true);
        return $texts[$locale]['descr_button']?? $texts['en']['descr_button']?? null;
    }

    public function getTargetTime(): string
    {
        if ($this->getType() === PromoItemType::SpecificDate) {
            return $this->getStartTime() < now()? $this->getEndTime() : $this->getStartTime();
        }
        if ($this->getType() === PromoItemType::WeekDay) {
            return mb_strtoupper($this->getStartTime()->dayName);
//            $today = Carbon::now();
//            $targetDayOfWeek = $this->getStartTime()->dayOfWeek;
//            if ($today->dayOfWeek === $targetDayOfWeek) return $today->endOfDay();
//            return $today->next($targetDayOfWeek)->startOfDay();
        }
        if ($this->getType() === PromoItemType::WeekDays) {
            return mb_strtoupper($this->getStartTime()->dayName).' - '.mb_strtoupper($this->getEndTime()->dayName);
//            $today = Carbon::now();
//            $targetDayOfWeek = $this->getEndTime()->dayOfWeek;
//            if ($today->dayOfWeek === $targetDayOfWeek) return $today->endOfDay();
//            return $today->next($targetDayOfWeek)->startOfDay();
        }
        return __('promos.indefinitely');
    }

    public function renderDescription(?string $locale = null): string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getDescriptions(), true);
        $description = $texts[$locale]['descr']?? $texts['en']['descr']?? null;
        return UI::textToHtml($description);
    }

}
