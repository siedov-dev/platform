<?php

namespace App\Models\Popups;

use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Mail\MailManager;
use App\Models\BonusAvailable;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\UserBonus;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property ?string $user_ids
 * @property string $category
 * @property string $img
 * @property string $locale_texts
 * @property Carbon $send_at
 * @property Carbon $available_to
 * @property boolean $send_mail
 * @property string $button_type
 * @property string $button_url
 * @property boolean $show_no_more
 * @property ?int $bonus_type
 * @property ?string $bonus_params
 * @property ?string $bonus_games
 * @property ?string $bonus_title
 * @property ?string $bonus_img
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property boolean $repeatable
 * @property string $repeat_period
 * @property int $repeat_times
 * @property Collection|BonusSegment[] $bonusSegments
 */
class UserPopupSchedule extends Model
{
    use HasFactory;

    protected $dates = [
        'send_at',
        'available_to',
        'created_at',
        'updated_at',
    ];

    const STATUS_PENDING = 0;
    const STATUS_SENDING = 1;
    const STATUS_DONE = 2;
    const STATUS_ERROR = 3;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserIds(): ?string
    {
        return $this->user_ids;
    }

    public function setUserIds(?string $user_ids): void
    {
        $this->user_ids = $user_ids;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    public function getLocaleTexts(): string
    {
        return $this->locale_texts;
    }

    public function setLocaleTexts(string $locale_texts): void
    {
        $this->locale_texts = $locale_texts;
    }

    public function getSendAt(): Carbon
    {
        return $this->send_at;
    }

    public function setSendAt(Carbon $send_at): void
    {
        $this->send_at = $send_at;
    }

    public function getAvailableTo(): Carbon
    {
        return $this->available_to;
    }

    public function setAvailableTo(Carbon $available_to): void
    {
        $this->available_to = $available_to;
    }

    public function isSendMail(): bool
    {
        return $this->send_mail;
    }

    public function setSendMail(bool $send_mail): void
    {
        $this->send_mail = $send_mail;
    }

    public function getButtonType(): string
    {
        return $this->button_type;
    }

    public function setButtonType(string $button_type): void
    {
        $this->button_type = $button_type;
    }

    public function getButtonUrl(): string
    {
        return $this->button_url;
    }

    public function setButtonUrl(string $button_url): void
    {
        $this->button_url = $button_url;
    }

    public function isShowNoMore(): bool
    {
        return $this->show_no_more;
    }

    public function setShowNoMore(bool $show_no_more): void
    {
        $this->show_no_more = $show_no_more;
    }

    public function getBonusType(): ?int
    {
        return $this->bonus_type;
    }

    public function setBonusType(?int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getBonusParams(): ?string
    {
        return $this->bonus_params;
    }

    public function setBonusParams(?string $bonus_params): void
    {
        $this->bonus_params = $bonus_params;
    }

    public function getBonusGames(): ?string
    {
        return $this->bonus_games;
    }

    public function setBonusGames(?string $bonus_games): void
    {
        $this->bonus_games = $bonus_games;
    }

    public function getBonusTitle(): ?string
    {
        return $this->bonus_title;
    }

    public function setBonusTitle(?string $bonus_title): void
    {
        $this->bonus_title = $bonus_title;
    }

    public function getBonusImg(): ?string
    {
        return $this->bonus_img;
    }

    public function setBonusImg(?string $bonus_img): void
    {
        $this->bonus_img = $bonus_img;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    public function setRepeatable(bool $repeatable): void
    {
        $this->repeatable = $repeatable;
    }

    public function getRepeatPeriod(): string
    {
        return $this->repeat_period;
    }

    public function setRepeatPeriod(string $repeat_period): void
    {
        $this->repeat_period = $repeat_period;
    }

    public function getRepeatTimes(): int
    {
        return $this->repeat_times;
    }

    public function setRepeatTimes(int $repeat_times): void
    {
        $this->repeat_times = $repeat_times;
    }

    public function getCrmStatusName(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Ожидание',
            self::STATUS_SENDING => 'Отправка',
            self::STATUS_ERROR => 'Ошибка',
            self::STATUS_DONE => 'Завершено',
            default => 'Unknown',
        };
    }

    public function render(?string $lang = null): string
    {
        if ($lang === null) $lang = app()->getLocale();
        $texts = json_decode($this->getLocaleTexts(), true);

        return view('common.popups.custom', [
            'img' => $this->getImg(),
            'title' => $texts[$lang]['title']?? $texts['en']['title'],
            'msg' => $texts[$lang]['message']?? $texts['en']['message'],
            'btn_text' => $texts[$lang]['button']?? $texts['en']['button'],
            'btn_type' => $this->getButtonType(),
            'url' => $this->getButtonUrl(),
            'unsub' => $this->isShowNoMore(),
            'category' => $this->getCategory(),
        ])->render();
    }

    public function renderMail(?string $lang = null): string
    {
        if ($lang === null) $lang = app()->getLocale();
        $texts = json_decode($this->getLocaleTexts(), true);

        return view('common.popups.mail', [
            'img' => $this->getImg(),
            'title' => $texts[$lang]['title']?? $texts['en']['title'],
            'msg' => $texts[$lang]['message']?? $texts['en']['message'],
            'btn_text' => $texts[$lang]['button']?? $texts['en']['button'],
            'btn_type' => $this->getButtonType(),
            'url' => $this->getButtonUrl(),
            'unsub' => $this->isShowNoMore(),
            'category' => $this->getCategory(),
        ])->render();
    }

    public static function processSchedule()
    {
        /** @var UserPopupSchedule $schedule */
        $schedule = UserPopupSchedule::where('status', self::STATUS_PENDING)
            ->where('send_at', '<', now()->format('Y-m-d H:i:s'))
            ->first();

        if ($schedule !== null) {
            $schedule->setStatus($schedule::STATUS_SENDING);
            $schedule->save();

            //mark old popups created by repeatable schedule as expired
            UserPopup::markExpired(UserPopup::CREATOR_TYPE_SCHEDULE, $schedule->getId());

            //create popups
            if (empty($schedule->getUserIds()) || empty(json_decode($schedule->getUserIds(), true))) {
                // global for no users selected
                foreach (Localization::getAvailableLocales() as $locale) {
                    UserPopup::createGlobal(
                        content: $schedule->render($locale),
                        category: $schedule->getCategory(),
                        priority: 0,
                        show_at: now(),
                        active_to: $schedule->getAvailableTo(),
                        locale: $locale,
                        creator_type: UserPopup::CREATOR_TYPE_SCHEDULE,
                        creator_id: $schedule->getId(),
                    );
                }
            } else {
                //personal for each user
                $ids = json_decode($schedule->getUserIds(), true);
                foreach ($ids as $id) {
                    /** @var MUser $user */
                    $user = MUser::find($id);
                    if ($user !== null) {
                        UserPopup::create(
                            user_id: $id,
                            content: $schedule->render($user->getLocale()),
                            category: $schedule->getCategory(),
                            priority: 0,
                            show_at: now(),
                            active_to: $schedule->getAvailableTo(),
                            creator_type: UserPopup::CREATOR_TYPE_SCHEDULE,
                            creator_id: $schedule->getId(),
                        );
                    }
                }
            }
            //create bonuses
            if ($schedule->getBonusType() > 0) {
                $ids = [];
                if (!empty($schedule->getUserIds())) {
                    $ids = json_decode($schedule->getUserIds(), true);
                }
                // if less than 10 users create bonuses
                if (!empty($ids) && count(json_decode($schedule->getUserIds(), true)) < 10) {
                    $texts = json_decode($schedule->getLocaleTexts(), true);
                    foreach ($ids as $id) {
                        try {
                            $bonus = UserBonus::fromParams(
                                bonus_type: $schedule->getBonusType(),
                                params: $schedule->getBonusParams(),
                                user_id: $id,
                            );
                            $bonus->title = $schedule->getBonusTitle();
                            $bonus->img_url = $schedule->getBonusImg();
                            $bonus->save();
                            if ($schedule->bonusSegments->count() > 0) {
                                $bonus->attachBonusSegments($schedule->bonusSegments->pluck('id')->toArray());
                            }
                        } catch (\Exception $e) {
                            Hlog::logException($e);
                        }
                    }
                } else {
                    try {
                        //create bonus available
                        $av_bonus = new BonusAvailable();
                        $av_bonus->setAvailableTo($schedule->getAvailableTo());
                        $av_bonus->setBonusType($schedule->getBonusType());
                        $av_bonus->setGames($schedule->getBonusGames());
                        $av_bonus->setParams($schedule->getBonusParams());
                        $av_bonus->setTitle($schedule->getBonusTitle());
                        $av_bonus->setImgUrl($schedule->getBonusImg());
                        $av_bonus->setAllowedUsers($schedule->getUserIds());
                        $av_bonus->save();
                        if ($schedule->bonusSegments->count() > 0) {
                            $av_bonus->bonusSegments()->sync($schedule->bonusSegments->pluck('id')->toArray());
                        }
                    } catch (\Exception $e) {
                        Hlog::logException($e);
                    }
                }
            }

            //send emails
            if ($schedule->isSendMail()) {
                try {
                    $texts = json_decode($schedule->getLocaleTexts(), true);
                    if (empty($schedule->getUserIds()) || empty(json_decode($schedule->getUserIds(), true))) {
                        foreach (Localization::getAvailableLocales() as $locale) {
                            MailManager::sendToAll(
                                title: $texts[$locale]['title']?? $texts['en']['title'],
                                content: $schedule->renderMail($locale),
                                locale: $locale,
                            );
                        }
                    } else {
                        foreach (Localization::getAvailableLocales() as $locale) {
                            MailManager::sendToIds(
                                ids: json_decode($schedule->getUserIds(), true),
                                title: $texts[$locale]['title']?? $texts['en']['title'],
                                content: $schedule->renderMail($locale),
                                locale: $locale,
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    Hlog::logException($e, 'mail');
                }
            }

            //process repeatable schedules
            if ($schedule->isRepeatable()) {
                // Parse repeat period and add to send_at and active_to.
                if (preg_match('#^(\d+)(\w+)?(\d)?$#', $schedule->getRepeatPeriod(), $matches)) {
                    // period in months
                    if ($matches[2]?? '' === 'M') {
                        $day = $matches[3]?? 1; //month date
                        $add_months = $matches[1]; //months to add
                        $newdate = $schedule->send_at->clone();
                        $newdate->addMonth()->startOfMonth()->addDays($day - 1); //set next month day
                        //if next month is shorter set end of month
                        if ($newdate > $schedule->send_at->clone()->addMonths($add_months)->endOfMonth()) {
                            $newdate = $schedule->send_at->clone()->addMonths($add_months)->endOfMonth();
                        }
                        //set hours and minutes
                        $newdate->setHours($schedule->send_at->hour);
                        $newdate->setMinutes($schedule->send_at->minute);
                        // set available to same time diff
                        $schedule->available_to = $newdate->clone()->addMinutes($schedule->available_to->diffInMinutes($schedule->send_at));
                        $schedule->send_at = $newdate;
                    } else {
                        //period time
                        $add_minutes = $matches[1] * self::periodMinutes($matches[2]??'');
                        $schedule->send_at = $schedule->send_at->addMinutes($add_minutes);
                        $schedule->available_to = $schedule->available_to->addMinutes($add_minutes);
                    }
                    if ($schedule->getRepeatTimes() > 0) {
                        $schedule->setRepeatTimes($schedule->getRepeatTimes() - 1);
                    }
                    if ($schedule->getRepeatTimes() === 0) {
                        $schedule->setStatus($schedule::STATUS_DONE);
                    } else {
                        $schedule->setStatus($schedule::STATUS_PENDING);
                    }
                    $schedule->save();
                } else {
                    //wrong period format
                    $schedule->setStatus($schedule::STATUS_ERROR);
                    $schedule->save();
                }
            } else {
                //mark as completed
                $schedule->setStatus($schedule::STATUS_DONE);
                $schedule->save();
            }
        }
    }

    /**
     * convert period symbol into minutes multiplier
     * @param string $symbol
     * @return int
     */
    public static function periodMinutes(string $symbol): int
    {
        return match ($symbol) {
            'h' => 60, //hours
            'd' => 60 * 24, //days
            'w' => 60 * 24 * 7, //weeks
            default => 1,
        };
    }

    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class, 'bonus_segment_user_popup_schedule');
    }
}
