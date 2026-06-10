<?php

namespace App\Models\Popups;

use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcTaskType;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @property int $id
 * @property int $user_id
 * @property int $priority
 * @property string $content
 * @property string $category
 * @property int $status
 * @property Carbon $show_at
 * @property Carbon $actual_to
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $locale
 * @property int $creator_type
 * @property int $creator_id
 */
class UserPopup extends Model
{
    use HasFactory;

    const GLOBAL_POPUP = -1;

    const CREATOR_TYPE_DEFAULT = 0;
    const CREATOR_TYPE_SCHEDULE = 1;

    /**
     * mark constructor as protected
     * so that it can't be created unless with static methods
     * @param array $attributes
     */
    protected function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $dates = [
        'show_at',
        'actual_to',
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getActualTo(): Carbon
    {
        return $this->actual_to;
    }

    public function getStatus(): PopupStatus
    {
        return PopupStatus::from($this->status);
    }

    public function setStatus(PopupStatus $status): UserPopup
    {
        $this->status = $status;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getShowAt(): Carbon
    {
        return $this->show_at;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setShowAt(Carbon $show_at): void
    {
        $this->show_at = $show_at;
    }

    public function setActualTo(Carbon $actual_to): void
    {
        $this->actual_to = $actual_to;
    }

    public function getCreatorType(): int
    {
        return $this->creator_type;
    }

    public function setCreatorType(int $creator_type): void
    {
        $this->creator_type = $creator_type;
    }

    public function getCreatorId(): int
    {
        return $this->creator_id;
    }

    public function setCreatorId(int $creator_id): void
    {
        $this->creator_id = $creator_id;
    }

    /**
     * create personal popup
     * @param int $user_id
     * @param string $content
     * @param string $category
     * @param int $priority
     * @param Carbon|null $show_at
     * @param Carbon|null $active_to
     * @return UserPopup
     */
    public static function create(int     $user_id,
                                  string  $content,
                                  string  $category = 'default',
                                  int     $priority = 0,
                                  ?Carbon $show_at = null,
                                  ?Carbon $active_to = null,
                                  int     $creator_type = 0,
                                  int     $creator_id = 0,
    ): ?UserPopup
    {
        if ($show_at === null) $show_at = now();
        if ($active_to === null) $active_to = now()->addMinutes(30);

        $popup = new UserPopup();
        $popup->setUserId($user_id);
        $popup->setContent($content);
        $popup->setCategory($category);
        $popup->setPriority($priority);
        $popup->setShowAt($show_at);
        $popup->setActualTo($active_to);
        $popup->setStatus(PopupStatus::New);
        $popup->setCreatorType($creator_type);
        $popup->setCreatorId($creator_id);
        $popup->save();

        //set in redis that user has a new popup message
        Redis::set($user_id . ':popup-check', true);

        return $popup;
    }

    /**
     * create global popup
     * this popup should be shown to everyone
     * @param string $content
     * @param string $category
     * @param int $priority
     * @param Carbon|null $show_at
     * @param Carbon|null $active_to
     * @return UserPopup
     */
    public static function createGlobal(string  $content,
                                        string  $category = 'default',
                                        int     $priority = 0,
                                        ?Carbon $show_at = null,
                                        ?Carbon $active_to = null,
                                        string  $locale = 'en',
                                        int     $creator_type = 0,
                                        int     $creator_id = 0,
    ): ?UserPopup
    {
        if ($show_at === null) $show_at = now();
        if ($active_to === null) $active_to = now()->addDay();

        $popup = new UserPopup();
        $popup->setUserId(self::GLOBAL_POPUP);
        $popup->setContent($content);
        $popup->setCategory($category);
        $popup->setPriority($priority);
        $popup->setShowAt($show_at);
        $popup->setActualTo($active_to);
        $popup->setStatus(PopupStatus::New);
        $popup->setLocale($locale);
        $popup->setCreatorType($creator_type);
        $popup->setCreatorId($creator_id);
        $popup->save();

        Redis::set('no-global-popups-' . $locale, false);

        return $popup;
    }

    /**
     * get current new popup
     * @param int $user_id
     * @return UserPopup|null
     */
    public static function getNew(MUser $user): ?UserPopup
    {
        $user_id = $user->getId();
        //if user has new popup message
        if (Redis::get($user_id . ":popup-check", true)) {
            try {
                //get all new popups
                $popups = self::where('user_id', $user_id)
                    ->where('show_at', '<=', now())
                    ->where('status', PopupStatus::New)
                    ->orderBy('priority')
                    ->get();

//                Log::info(var_export($popups, true));

                // if there are no new messages to show
                if ($popups->count() === 0) {
                    // and no planned messages
                    $popups = self::where('user_id', $user_id)
                        ->where('status', PopupStatus::New)
                        ->orderBy('priority')
                        ->get();
                    // disable popup checking
                    if ($popups->count()) Redis::set($user_id . ":popup-check", false);
                }

                /** @var UserPopup $popup */
                foreach ($popups as $popup) {

                    //if popup isn't actual mark expired and try next
                    if ($popup->actual_to < now()) {
                        $popup->setStatus(PopupStatus::Expired)->save();
                        continue;
                    }

                    //mark unsubscribed category popups as disabled
                    if (PopupUnsubscribe::isUserUnsubscribed($user_id, $popup->getCategory())) {
                        $popup->setStatus(PopupStatus::Disabled)->save();
                        continue;
                    }

                    // mark as shown and return popup
                    $popup->setStatus(PopupStatus::Shown)->save();
                    return $popup;
                }
            } catch (\Exception $e) {
                Hlog::logException($e);
            }
        }

        if (!Redis::get('no-global-popups-' . $user->getLocale(), false)) {

            //check global popups
            try {
                $last_shown_at = Redis::get($user_id . ":last_gpopup_at");
                if (empty($last_shown_at)) $last_shown_at = "2000-01-01 00:00:00";
                //get all current global popups
                $popups = self::where('user_id', self::GLOBAL_POPUP)
                    ->where('show_at', '<=', now())
                    ->where('show_at', '>', $last_shown_at)
                    ->where('status', PopupStatus::New)
                    ->where('locale', $user->getLocale())
                    ->orderBy('priority')
                    ->get();

                Redis::set($user_id . ":last_gpopup_at", now()->format('Y-m-d H:i:s'));

                /** @var UserPopup $popup */
                foreach ($popups as $popup) {

                    //if popup isn't actual mark expired and try next
                    if ($popup->actual_to < now()) {
                        $popup->setStatus(PopupStatus::Expired)->save();
                        continue;
                    }

                    //skip unsubscribed categories
                    if (PopupUnsubscribe::isUserUnsubscribed($user_id, $popup->getCategory())) {
                        continue;
                    }

                    // if global popup wasn't shown mark as shown and create personal popup
                    if (!PopupsGlobalShown::wasPopupShown($popup->getId(), $user_id)) {
                        PopupsGlobalShown::setPopupShown($popup->getId(), $user_id);
                        //create personal popup
                        self::create(
                            user_id: $user_id,
                            content: $popup->getContent(),
                            category: $popup->getCategory(),
                            priority: $popup->getPriority(),
                            show_at: $popup->getShowAt(),
                            active_to: $popup->getActualTo(),
                            creator_type: $popup->getCreatorType(),
                            creator_id: $popup->getCreatorId());
                        //show right after
                        self::setTimeout($user_id, 0);
                    }
                }
            } catch (\Exception $e) {
                Hlog::logException($e);
            }
        }

        return null;
    }

    public static function hasUnreadCategory(int $user_id, string $category): bool
    {
        return self::where('user_id', $user_id)
                ->where('show_at', '<=', now())
                ->where('status', PopupStatus::New)
                ->where('category', $category)
                ->orderBy('priority')
                ->first() !== null;
    }

    public static function showTelegramPopup(int $user_id, $show_in_secs = 20): ?UserPopup
    {
        return null;
        //check custom popup
        /** @var CustomPopup $tg_popup */
        $tg_popup = CustomPopup::where('category', 'telegram')->first();
        if ($tg_popup !== null) {
            $user = MUser::find($user_id);
            return UserPopup::create($user_id, $tg_popup->render($user?->locale), 'telegram',
                0, now()->addSeconds($show_in_secs), now()->addMinutes(10));
        }
        return UserPopup::create($user_id, view('common.popups.telegram')->render(), 'telegram',
            0, now()->addSeconds($show_in_secs), now()->addMinutes(10));
    }

    public static function showBirthdayPopup(MUser $user): ?UserPopup
    {
        //check custom popup
        /** @var CustomPopup $b_popup */
        $b_popup = CustomPopup::where('category', 'birthday')->first();
        if ($b_popup !== null) {
            return UserPopup::create($user->getId(), $b_popup->render($user?->locale), 'birthday', 0, now(), now()->addDay());
        }
        return UserPopup::create($user->getId(), view('common.popups.birthday')->render(), 'birthday', 3, now(), now()->addDay());
    }

    public static function showTaskStarterPopup(int $user_id): ?UserPopup
    {
        //check custom popup
        /** @var CustomPopup $c_popup */
        $c_popup = CustomPopup::where('category', 'start-tasks')->first();
        if ($c_popup !== null) {
            $user = MUser::find($user_id);
            return UserPopup::create($user->getId(), $c_popup->render($user?->locale), 'tasks', 0, now(), now()->addDay());
        }

        $task_checker = SlcTaskType::FirstSteps->getTaskChecker();
        $slc_task = SlcTask::getByType(SlcTaskType::FirstSteps);
        $reward = $task_checker->getReward($slc_task);
        $popup = UserPopup::create($user_id, view('common.popups.tasks_starter', ['slc_item' => $reward])->render(), 'tasks',
            0, now(), now()->addMinutes(120));
        self::setTimeout($user_id, 0);
        return $popup;
    }

    public static function showCompletedTasks(int $user_id): ?UserPopup
    {
        //check custom popup
        /** @var CustomPopup $c_popup */
        $c_popup = CustomPopup::where('category', 'tasks-completed')->first();
        if ($c_popup !== null) {
            $user = MUser::find($user_id);
            return UserPopup::create($user->getId(), $c_popup->render($user?->locale), 'tasks', 0, now(), now()->addDay());
        }

        self::setTimeout($user_id, 0);
        $task_checker = SlcTaskType::FirstSteps->getTaskChecker();
        $slc_task = SlcTask::getByType(SlcTaskType::FirstSteps);
        $reward = $task_checker->getReward($slc_task);
        return UserPopup::create($user_id, view('common.popups.tasks_completed', [
            'item' => $reward,
            'info' => $reward->getInfo(),
        ])->render(), 'tasks', 1, now(), now()->addMinutes(120));
    }

    public static function showNewRank(MUser $user, int $rank_level): void
    {
        $a_loc = App::getLocale();
        Localization::setAppLocale($user->getLocale());
        self::setTimeout($user->getId(), 0);
        UserPopup::create($user->getId(), view('common.popups.new_rank', [
            'rank_level' => $rank_level,
            'user' => $user,
        ])->render(), 'ranks', 1, now(), now()->addMinutes(10));
        Localization::setAppLocale($a_loc);
    }

    public static function showPopularSlots(int $user_id): ?UserPopup
    {
        //check custom popup
        /** @var CustomPopup $c_popup */
        $c_popup = CustomPopup::where('category', 'popular-slots')->first();
        if ($c_popup !== null) {
            $user = MUser::find($user_id);
            return UserPopup::create($user->getId(), $c_popup->render($user?->locale), 'tasks', 0, now(), now()->addDay());
        }
        return null;
    }

    public static function setTimeout(int $user_id, int $seconds = 10): void
    {
        $timeout_to = now()->addSeconds($seconds);
        Redis::set($user_id . ':popups:timeout', $timeout_to->format('Y-m-d H:i:s'));
//        Log::info($user_id.' popup new timeout '.$timeout_to);
    }

    public static function prolongTimeout(int $user_id, int $seconds = 10): void
    {
        $timeout_to = Redis::get($user_id . ':popups:timeout');
        if ($timeout_to === null) {
            Redis::set($user_id . ':popups:timeout', now()->addSeconds($seconds));
        }
        $timeout_to = Carbon::parse($timeout_to)->addSeconds($seconds);
        Redis::set($user_id . ':popups:timeout', $timeout_to->format('Y-m-d H:i:s'));
//        Log::info($user_id.' popup prolonged timeout '.$timeout_to);
    }

    public static function markExpired(int $creator_type, int $creator_id): void
    {
        UserPopup::where('creator_type', $creator_type)
            ->where('creator_id', $creator_id)
            ->update(['status' => PopupStatus::Expired->value]);
    }

    public static function clearOld(): void
    {
        UserPopup::where('actual_to', '<' , now()->format('Y-m-d H:i:s'))
            ->update(['status' => PopupStatus::Expired->value]);

        UserPopup::where('created_at', '<' , now()->subDays(7)->format('Y-m-d H:i:s'))
            ->whereIn('status', [
                PopupStatus::Expired->value,
                PopupStatus::Shown->value,
                PopupStatus::Disabled->value,
                ])
            ->delete();
    }
}
