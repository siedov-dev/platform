<?php

namespace App\Models;

use App\Helpers\Device;
use App\MUser;
use App\Payin\Payin;
use App\Payout\Payout;
use App\Services\KPay\KPay;
use App\Services\KPay\UserStatsData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class PaymentSystems
 * Платежные методы.
 *
 * @property int $id // id БД
 * @property int $type // тип 0 - депозит, 1 - выплата
 * @property int $currency_id // ид валюты
 * @property int $system_id // ид системы Payin::TYPE, Payout::TYPE
 * @property string $system_name // название системы
 * @property string $name // название метода
 * @property string $method_alias // алиас метода
 * @property string $image_url // иконка
 * @property string $bank_alias // алиас банка (используется некоторыми платежами)
 * @property float $min_sum // минимальная сумма пополнения/выплаты
 * @property float $max_sum  // максимальная сумма пополнения/выплаты
 * @property float $tax // не используется
 * @property bool $disabled // отключена
 * @property int $priority // приоритет отображения
 * @property bool $is_hidden // скрыта
 * @property bool $has_reqs //
 * @property bool $reqs_enabled
 * @property int $traffic // не используется
 * @property bool $test_mode // только для тестеров
 * @property PaymentSystemGroup $system_group // группа PaymentSystemGroup
 * @property bool $block_multideps // блокировать мультидепы
 * @property int $block_after_deps // блокировать через к-во депозитов
 * @property int $block_minutes // время блокировки в минутах
 * @property bool $change_amount // изменять сумму депозита (необходимо для некоторых систем), добавляет 1-10 к сумме депозита
 * @property bool $email_req // необходима верифицированная почта
 * @property bool $phone_req // необходим верифицированный номер телефона
 */
class PaymentSystems
{
    public int $id;
    public int $type;
    public int $currency_id;
    public int $system_id;
    public string $system_name;
    public string $name;
    public string $method_alias;
    public string $image_url;
    public string $bank_alias;
    public float $min_sum;
    public float $max_sum;
//    public float $tax;
    public bool $disabled;
    public int $priority;
    public bool $is_hidden;
    public bool $has_reqs;
    public bool $reqs_enabled;
//    private int $traffic;
    public bool $test_mode;
    public PaymentSystemGroup $system_group;
    public bool $block_multideps;
    public int $block_after_deps;
    public int $block_minutes;
    public bool $change_amount;
    public bool $email_req;
    public bool $phone_req;

//    protected $casts = [
//        'system_group' => PaymentSystemGroup::class,
//    ];

    const TYPE_PAYIN = 0;
    const TYPE_PAYOUT = 1;
    const TYPE_CRYPTO_BUY = 2;

    private static $_systems = null;
    private $_payin_system = null;
    private $_payout_system = null;

    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? 0;
        $this->type = $attributes['type'] ?? 0;
        $this->currency_id = $attributes['currency_id'] ?? 0;
        $this->system_id = $attributes['system_id'] ?? 0;
        $this->system_name = $attributes['system_name'] ?? '';
        $this->name = $attributes['name'] ?? '';
        $this->method_alias = $attributes['method_alias'] ?? '';
        $this->image_url = $attributes['image_url'] ?? '';
        $this->bank_alias = $attributes['bank_alias'] ?? '';
        $this->min_sum = $attributes['min_sum'] ?? 0;
        $this->max_sum = $attributes['max_sum'] ?? 0;
//        $this->tax = $attributes['tax'] ?? 0;
        $this->disabled = $attributes['disabled'] ?? false;
        $this->priority = $attributes['priority'] ?? 0;
        $this->is_hidden = $attributes['is_hidden'] ?? false;
        $this->has_reqs = $attributes['has_reqs'] ?? false;
        $this->reqs_enabled = $attributes['reqs_enabled'] ?? false;
//        $this->traffic = $attributes['traffic'] ?? 0;
        $this->test_mode = $attributes['test_mode'] ?? false;
        $this->system_group = PaymentSystemGroup::tryFrom($attributes['system_group']) ?? PaymentSystemGroup::NoGroup;
        $this->block_multideps = $attributes['block_multideps'] ?? false;
        $this->block_after_deps = $attributes['block_after_deps'] ?? 0;
        $this->block_minutes = $attributes['block_minutes'] ?? 0;
        $this->change_amount = $attributes['change_amount'] ?? false;
        $this->email_req = $attributes['email_req'] ?? false;
        $this->phone_req = $attributes['phone_req'] ?? false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    /**
//     * @return PaymentMethod[]|\Illuminate\Database\Eloquent\Collection|null
//     */
//    public static function getSystems()
//    {
//        if (self::$_systems === null) {
//            self::$_systems = PaymentMethod::all();
//        }
//        return self::$_systems;
//    }

    /**
     * @param $currency_id
     * @param bool $hidden
     * @return Collection|PaymentSystems[]
     */
    public static function getPayinSystemsForCurrency($currency_id, bool $hidden = false, ?PaymentSystemGroup $group = null): Collection
    {
//        if ($hidden) {
//            return self::getSystems()->where('type', self::TYPE_PAYIN)
//                ->where('currency_id', $currency_id)
//                ->sortBy('priority');
//        }
//        $data = self::getSystems()->where('type', self::TYPE_PAYIN)
//            ->where('currency_id', $currency_id)
//            ->where('is_hidden', false);
//
//        if ($group !== null) {
//            $data = $data->where('system_group', $group);
//        }
        $group_id = -1;
        $test = false;
        if ($group !== null) {
            $group_id = $group->value;
        }
        if (Auth::check() && in_array(Auth::id(), TestUser::getUserIds())) {
            $test = true;
        }
        if (Auth::check()) {
            $userData = UserStatsData::getUserData(Auth::id());
            $data = KPay::getInstance()->getPayMethods(Auth::id(), $currency_id, self::TYPE_PAYIN, $test, $group_id, $userData);
        } else {
            $data = KPay::getInstance()->getPayMethods(Auth::id(), $currency_id, self::TYPE_PAYIN, $test, $group_id);
        }

//        if (Auth::check()) {
//
//            $payed = UserMoneyStat::getStat(MUser::getAuth(), $currency_id, UserMoneyStat::TYPE_TOTAL);
//
//            /** @var PaymentMethod $system */
//            foreach ($data as $key => $system) {
//                if ($system->getPayin()->isHidden($currency_id, $payed?->getDepositCount()?? 0, $payed?->getDepositSum()?? 0, $system)) {
//                    $data->forget($key);
//                }
//            }
//        }

//        $data = $data->sortBy('id')->sortBy('priority', 0, true);

        return $data;
    }

    public static function getPayoutSystemsForCurrency($currency_id, bool $hidden = false, ?PaymentSystemGroup $group = null)
    {
        $group_id = -1;
        $test = false;
        if ($group !== null) {
            $group_id = $group->value;
        }
        if (Auth::check() && in_array(Auth::id(), TestUser::getUserIds())) {
            $test = true;
        }

        if (Auth::check()) {
            $userData = UserStatsData::getUserData(Auth::id());
            $data = KPay::getInstance()->getPayMethods(Auth::id()?? 0, $currency_id, self::TYPE_PAYOUT, $test, $group_id, $userData);
        } else {
            $data = KPay::getInstance()->getPayMethods(Auth::id()?? 0, $currency_id, self::TYPE_PAYOUT, $test, $group_id);
        }

        $data = $data->sortBy('id')->sortBy('priority', 0, true);

        return $data;
//        if ($hidden) {
//            return self::getSystems()->where('type', self::TYPE_PAYOUT)
//                ->where('currency_id', $currency_id)
//                ->sortBy('priority');
//        }
//        $data = self::getSystems()->where('type', self::TYPE_PAYOUT)
//            ->where('currency_id', $currency_id)
//            ->where('is_hidden', false);
//
//        if ($group !== null) {
//            $data = $data->where('system_group', $group);
//        }
//
//        if (Auth::check()) {
//            if (!in_array(Auth::id(), TestUser::getUserIds())) {
//                $data = $data->where('test_mode', false);
//            }
//        }
//
//        $data = $data->sortBy('priority');
//
//        return $data;
    }

    public static function find(int $id): ?PaymentSystems
    {
        return KPay::getInstance()->getPayMethodById($id);
    }

    public static function getDepositGroups(int $currency): Collection
    {
        $test = false;
        if (in_array(Auth::id(), TestUser::getUserIds())) {
            $test = true;
        };
        $groups = KPay::getInstance()->getPayGroups($currency, self::TYPE_PAYIN, $test);
        foreach ($groups as $id => $group) {
            $groups[$id]['name'] = PaymentSystemGroup::from($group['group_id'])->name;
            $groups[$id]['value'] = PaymentSystemGroup::from($group['group_id'])->value;
        }
        return collect($groups);
//        $qr = PaymentMethod::query();
//        $qr->where('currency_id', $currency);
//        $qr->where('system_group', '!=', PaymentSystemGroup::NoGroup->value);
//        $qr->selectRaw('system_group, count(system_group) as systems, min(min_sum) as min_sum, max(max_sum) as max_sum');
//        $qr->where('type', self::TYPE_PAYIN);
//        $qr->where('is_hidden', false);
//        $qr->groupBy('system_group');
//        if (!in_array(Auth::id(), TestUser::getUserIds())) {
//            $qr->where('test_mode', false);
//        }
//        $qr->having('systems', '>', 0);
//        $groups = $qr->get();
//
//        //check for not available for user
//        /** @var PaymentMethod $system */
//        foreach ($groups as $key => $group) {
//            $systems = PaymentMethod::getPayinSystemsForCurrency(\App\MUser::getAuth()->getCurrency(), false, $group->system_group);
//            if ($systems->count() == 0) {
//                $groups->forget($key);
//            }
//        }
//        return $groups;
    }

    public static function getPayoutGroups(int $currency): Collection
    {
        $test = false;
        if (in_array(Auth::id(), TestUser::getUserIds())) {
            $test = true;
        };
        $groups = KPay::getInstance()->getPayGroups($currency, self::TYPE_PAYOUT, $test);
        foreach ($groups as $id => $group) {
            $groups[$id]['name'] = PaymentSystemGroup::from($group['group_id'])->name;
            $groups[$id]['value'] = PaymentSystemGroup::from($group['group_id'])->value;
        }
        return collect($groups);
//        $qr = PaymentMethod::query();
//        $qr->where('currency_id', $currency);
//        $qr->where('system_group', '!=', PaymentSystemGroup::NoGroup->value);
//        $qr->selectRaw('system_group, count(system_group) as systems, min(min_sum) as min_sum, max(max_sum) as max_sum');
//        $qr->where('type', self::TYPE_PAYOUT);
//        $qr->where('is_hidden', false);
//        $qr->groupBy('system_group');
//        if (!in_array(Auth::id(), TestUser::getUserIds())) {
//            $qr->where('test_mode', false);
//        }
//        $qr->having('systems', '>', 0);
//        $groups = $qr->get();
//
//        //check for not available for user
//        /** @var PaymentMethod $system */
//        foreach ($groups as $key => $group) {
//            $systems = PaymentMethod::getPayinSystemsForCurrency(\App\MUser::getAuth()->getCurrency(), false, $group->system_group);
//            if ($systems->count() == 0) {
//                $groups->forget($key);
//            }
//        }
//        return $groups;
    }

    /**
     * @param $currency_id
     * @param bool $hidden
     * @return mixed
     * @throws \Exception
     * @deprecated
     */
    public static function getBuyCryptoForCurrency($currency_id, bool $hidden = false)
    {
        if ($hidden) {
            return self::getSystems()->where('type', self::TYPE_CRYPTO_BUY)
                ->where('currency_id', $currency_id)
                ->sortBy('priority');
        }
        $data = self::getSystems()->where('type', self::TYPE_CRYPTO_BUY)
            ->where('currency_id', $currency_id)
            ->where('is_hidden', false);

        if (Auth::check()) {
            if (!in_array(Auth::id(), TestUser::getUserIds())) {
                $data = $data->where('test_mode', false);
            }

//            $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
//                ->where('user_id', Auth::id())
//                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
//                ->groupBy('user_id')
//                ->first();
            $payed = UserMoneyStat::getStat(MUser::getAuth(), $currency_id, UserMoneyStat::TYPE_TOTAL);

            /** @var PaymentSystems $system */
            foreach ($data as $key => $system) {
                if ($system->getPayin()->isHidden($currency_id, $payed?->getDepositCount()?? 0, $payed?->getDepositSum()?? 0, $system)) {
                    $data->forget($key);
                }
            }
        }

        $data = $data->sortBy('priority');

        return $data;
    }

    /**
     * @param string $method_alias
     * @param int $currency_id
     * @return PaymentSystems|null
     */
    public static function getPayinSystemForMethod(string $method_alias, int $currency_id = 0): ?PaymentSystems
    {
        return KPay::getInstance()->getPayMethodByTypeAndAlias(self::TYPE_PAYIN, $method_alias, $currency_id);
//        return self::getSystems()->where('type', self::TYPE_PAYIN)
//            ->where('method_alias', $method_alias)->first();
    }

    public static function getPaySystemById($method_id): ?PaymentSystems
    {
        return self::find($method_id);
    }

    /**
     * @param string $method_alias
     * @param int $currency_id
     * @return PaymentSystems|null
     */
    public static function getPayoutSystemForMethod(string $method_alias, int $currency_id = 0): ?PaymentSystems
    {
        return KPay::getInstance()->getPayMethodByTypeAndAlias(self::TYPE_PAYOUT, $method_alias, $currency_id);
//        return self::getSystems()->where('type', self::TYPE_PAYOUT)
//            ->where('method_alias', $method_alias)->first();
    }

    /**
     * @return Payin
     * @throws \Exception
     */
    public function getPayin(): Payin
    {
        if ($this->_payin_system === null) {
            $payin = new Payin(Auth::user());
            $this->_payin_system = $payin->getPayinSystem($this->system_id);
        }
        return $this->_payin_system;
    }

    /**
     * @return Payout
     * @throws \Exception
     */
    public function getPayout(): Payout
    {
        if ($this->_payout_system === null) {
            $this->_payout_system = Payout::getPayoutSystem($this->system_id);
        }
        return $this->_payout_system;
    }

    /**
     * method is blocked by code
     * when fail pass additional requirements, etc.
     * @return bool
     * @throws \Exception
     */
    public function isBlocked(): bool
    {
        if (!$this->hasAdditionCheck() || !$this->additionalCheck()) return false;
        if ($this->type == self::TYPE_PAYIN) return $this->getPayin()->isBlocked($this->method_alias);
        if ($this->type == self::TYPE_PAYOUT) return $this->getPayout()->isBlocked($this->method_alias);
        return false;
    }

    /**
     * block reason
     * @return string
     * @throws \Exception
     */
    public function getBlockReason(): string
    {
        if ($this->type == self::TYPE_PAYIN) return $this->getPayin()->blockReason($this->method_alias);

        return "";
    }

    /**
     * method is disabled but shown in payment list
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * method is hidden in payment list
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    /**
     * payment method has additional requirements
     * @return bool
     */
    public function hasAdditionCheck(): bool
    {
        return $this->has_reqs;
    }

    /**
     * addditional requirements check enabled
     * @return bool
     */
    public function additionalCheck(): bool
    {
        return $this->reqs_enabled;
    }

    public function isTestMode(): bool
    {
        return $this->test_mode;
    }

    /**
     * @return float
     */
    public function getMinSum(): float
    {
        return $this->min_sum;
    }

    /**
     * @return float
     */
    public function getMaxSum(): float
    {
        return $this->max_sum;
    }

    public static function getTypeName(int $type): string
    {
        return match ($type) {
            self::TYPE_PAYIN => 'Прием',
            self::TYPE_PAYOUT => 'Выплата',
            self::TYPE_CRYPTO_BUY => 'Покупка крипты', //deprecated
            default => 'n/a',
        };
    }

    public function getSystemGroup(): PaymentSystemGroup
    {
        return $this->system_group;
    }

    public function setSystemGroup(PaymentSystemGroup $system_group): void
    {
        $this->system_group = $system_group;
    }

    public function isBlockMultideps(): bool|null
    {
        return $this->block_multideps;
    }

    public function setBlockMultideps(bool $block_multideps): void
    {
        $this->block_multideps = $block_multideps;
    }

    public function getBlockAfterDeps(): int
    {
        return $this->block_after_deps;
    }

    public function setBlockAfterDeps(int $block_after_deps): void
    {
        $this->block_after_deps = $block_after_deps;
    }

    public function getBlockMinutes(): int
    {
        return $this->block_minutes;
    }

    public function setBlockMinutes(int $block_minutes): void
    {
        $this->block_minutes = $block_minutes;
    }
//
//    public function currency(): HasOne
//    {
//        return $this->hasOne(UserCurrency::class, 'id', 'currency_id');
//    }

    public function setChangeAmount(bool $change_amount): void
    {
        $this->change_amount = $change_amount;
    }

    public function shouldChangeAmount(): bool
    {
        return $this->change_amount;
    }

    public function isEmailReq(): bool
    {
        return $this->email_req;
    }

    public function setEmailReq(bool $email_req): void
    {
        $this->email_req = $email_req;
    }

    public function isPhoneReq(): bool
    {
        return $this->phone_req;
    }

    public function setPhoneReq(bool $phone_req): void
    {
        $this->phone_req = $phone_req;
    }

}
