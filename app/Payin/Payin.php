<?php


namespace App\Payin;


use App\Events\MoneyDeposit;
use App\Events\MoneySpent;
use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Models\Bonuses\KUserBonus;
use App\Models\DepositParams;
use App\Models\Slc\UserTasks\UserTaskType;
use App\ResultOrErr;
use App\Services\KPay\KPay;
use App\Services\KSlc\KSlc;
use App\Services\NatsService\NatsUserEvents;
use App\Services\Users\UserManager;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\DepositBonusInterface;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\PaymentSystemBlock;
use App\Models\PaymentSystems;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Payin
{
    const TYPE_PIASTRIX = 1;
    const TYPE_PAYEER = 2;
    const TYPE_ENOT = 3;
    const TYPE_PM = 4;
    const TYPE_BILLINE = 5;
    const TYPE_OBMENKA = 6;
    const TYPE_SETTLEPAY = 7;
    const TYPE_FREEKASSA = 8;
    const TYPE_ALIKASSA = 9;
    const TYPE_BRPAY = 10;
    const TYPE_SWIFT = 11;
    const TYPE_IKASSA = 12;
    const TYPE_IKINR = 13;
    const TYPE_PAZE = 14;
    const TYPE_PAYCOS = 15;
    const TYPE_PAYGATE = 16;
    const TYPE_CRYPTOCLOUD = 17;
    const TYPE_ALPHAPO = 18;
    const TYPE_KVITUM = 19;
    const TYPE_IKINR_PTP = 20;
    const TYPE_IKKZT = 21;
    const TYPE_PAYLAMA = 22;
    const TYPE_IKINR3 = 23;
    const TYPE_EINPAYZ = 24;
    const TYPE_PAYKASSA = 25;
    const TYPE_CRYPTOMUS = 26;
    const TYPE_ALFAKIT = 27;
    const TYPE_BETATRANSFER = 28;
    const TYPE_SREDAPAY = 29;
    const TYPE_ANTAREX = 30;
    const TYPE_PAYCENTER = 31;
    const TYPE_B2PAY = 32;
    const TYPE_POSTMAN = 33;
    const TYPE_INWIZO = 34;
    const TYPE_AEZA = 35;
    const TYPE_PAYDEX = 36;
    const TYPE_SANDBOX = 37;
    const TYPE_AIFORY = 38;
    const TYPE_NASHOBMEN = 39;
    const TYPE_GATEEXPRESS = 40;
    const TYPE_EMPAYERE = 41;
    const TYPE_CROSSPAY = 42;
    const TYPE_CHATLPAY = 43;
    const TYPE_EXPAY = 45;
    const TYPE_PAYCROWN = 46;
    const TYPE_CHANGELLY = 47;
    const TYPE_ANYPAY = 48;
    const TYPE_PASSIMPAY = 49;
    const TYPE_PAYADMIT = 50;
    const TYPE_BILLBLEND = 51;
    const TYPE_BESTPAY24 = 52;
    const TYPE_CHASSE = 53;
    const TYPE_SKYPAY = 54;
    const TYPE_PAYCRAFT = 55;
    const TYPE_MAYFPAY = 56;
    const TYPE_PAYONHOST = 57;
    const TYPE_REPAY = 58;
    const TYPE_STREAMPAY = 59;
    const TYPE_TURCODE = 60;
    const TYPE_XPAYPRO = 61;
    const TYPE_JUPITER = 62;
    const TYPE_COOCOOPAY = 63;
    const TYPE_LUCKYPAY = 64;
    const TYPE_BITZONE = 65;
    const TYPE_PAYME = 66;
    const TYPE_PAYSCROW = 67;
    const TYPE_EASYBITS = 68;
    const TYPE_DUALI_PAY = 69;
    const TYPE_NIRVANA_PAY = 70;
    const TYPE_INFINITY_PAY = 71;
    const TYPE_ONEPLAT_PAY = 72;
    const TYPE_ONEVISION = 73;
    const TYPE_HIGHHELP = 74;
    const TYPE_SHADOWSPAYS = 75;
    const TYPE_SEQUOIAPAY = 76;
    const TYPE_EXOTIC = 77;
    const TYPE_SAREX = 78;
    const TYPE_PSPY = 79;
    const TYPE_PROCTECH = 80;
    const TYPE_PAYGURU = 81;
    const TYPE_FENI = 82;
    const TYPE_NOROS = 83;

    protected string $name;
    protected int $type;
    protected string $currency_name;
    /** @var MUser */
    protected $user;

    protected $_error = null;

    /**
     * Возвращает ошибку возникшую при депозите
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->_error;
    }

    public function __construct(MUser $user)
    {
        $this->user = $user;
        $this->currency_name = $user->getCurrencyName();
        $this->name = "base class";
    }

    /**
     * Возвращает последнее значение указанного ревкизита
     * @param $requisite
     * @return mixed|null
     */
    public function getLastRequisite($requisite)
    {
        return $this->user->getUserData()->getValue('last_deposit_data', [])[$this->getType()][$requisite] ?? null;
    }

    /**
     * Возвращает систему депозита по ид
     * @param int $system
     * @return Payin
     */
    public function getPayinSystem(int $system): Payin
    {
        return match ($system) {
            self::TYPE_COOCOOPAY => new CooCooPayin($this->user),
            self::TYPE_PIASTRIX => new PiastrixPayin($this->user),
            self::TYPE_PAYEER => new PayeerPayin($this->user),
            self::TYPE_ENOT => new EnotPayin($this->user),
            self::TYPE_PM => new PerfectMoneyPayin($this->user),
            self::TYPE_BILLINE => new BillLinePayin($this->user),
            self::TYPE_OBMENKA => new ObmenkaUaPayin($this->user),
            self::TYPE_SETTLEPAY => new SettlePayin($this->user),
            self::TYPE_FREEKASSA => new FreeKassaPayin($this->user),
            self::TYPE_ALIKASSA => new AliKassaPayin($this->user),
            self::TYPE_BRPAY => new BrpayPayin($this->user),
            self::TYPE_SWIFT => new SwiftPayin($this->user),
            self::TYPE_IKASSA => new InterKassaPayin($this->user),
            self::TYPE_IKINR => new IkInrPayin($this->user),
            self::TYPE_PAZE => new PazePayin($this->user),
            self::TYPE_PAYCOS => new PaycosPayin($this->user),
            self::TYPE_PAYGATE => new PaygatePayin($this->user),
            self::TYPE_CRYPTOCLOUD => new CryptoCloudPayin($this->user),
            self::TYPE_ALPHAPO => new AlphapoPayin($this->user),
            self::TYPE_KVITUM => new KvitumPayin($this->user),
            self::TYPE_IKINR_PTP => new IkInrPtpPayin($this->user),
            self::TYPE_IKKZT => new IkKztPayin($this->user),
            self::TYPE_PAYLAMA => new PayLamaPayin($this->user),
            self::TYPE_IKINR3 => new IkInr3Payin($this->user),
            self::TYPE_EINPAYZ => new EinpayzPayin($this->user),
            self::TYPE_PAYKASSA => new PayKassaPayin($this->user),
            self::TYPE_CRYPTOMUS => new CryptomusPayin($this->user),
            self::TYPE_ALFAKIT => new AlfakitPayin($this->user),
            self::TYPE_BETATRANSFER => new BetatransferPayin($this->user),
            self::TYPE_SREDAPAY => new SredaPayPayin($this->user),
            self::TYPE_ANTAREX => new AntarexPayin($this->user),
            self::TYPE_PAYCENTER => new PayCenterPayin($this->user),
            self::TYPE_B2PAY => new B2PayPayin($this->user),
            self::TYPE_POSTMAN => new PostmanPayin($this->user),
            self::TYPE_INWIZO => new InwizoPayin($this->user),
            self::TYPE_AEZA => new AezaPayin($this->user),
            self::TYPE_PAYDEX => new PaydexPayin($this->user),
            self::TYPE_SANDBOX => new SandboxPayin($this->user),
            self::TYPE_AIFORY => new AiforyPayin($this->user),
            self::TYPE_NASHOBMEN => new NashObmenPayin($this->user),
            self::TYPE_GATEEXPRESS => new GateExpressPayin($this->user),
            self::TYPE_EMPAYERE => new EmpayrePayin($this->user),
            self::TYPE_CROSSPAY => new CrossPayPayin($this->user),
            self::TYPE_CHATLPAY => new ChatlPayPayin($this->user),
            self::TYPE_EXPAY => new ExPayPayin($this->user),
            self::TYPE_PAYCROWN => new PayCrownPayin($this->user),
            self::TYPE_CHANGELLY => new ChangellyPayin($this->user),
            self::TYPE_PASSIMPAY => new PassimpayPayin($this->user),
            self::TYPE_PAYADMIT => new PayadmitPayin($this->user),
            self::TYPE_ANYPAY => new AnyPayPayin($this->user),
            self::TYPE_BILLBLEND => new BillBlendPayin($this->user),
            self::TYPE_BESTPAY24 => new BestPay24Payin($this->user),
            self::TYPE_CHASSE => new ChassePayin($this->user),
            self::TYPE_SKYPAY => new SkyPayPayin($this->user),
            self::TYPE_PAYCRAFT => new PayCraftPayin($this->user),
            self::TYPE_MAYFPAY => new MayfPayPayin($this->user),
            self::TYPE_PAYONHOST => new PayOnHostPayin($this->user),
            self::TYPE_REPAY => new RepayPayin($this->user),
            self::TYPE_STREAMPAY => new StreamPayPayin($this->user),
            self::TYPE_TURCODE => new TurcodePayin($this->user),
            self::TYPE_XPAYPRO => new XPayProPayin($this->user),
            self::TYPE_JUPITER => new JupiterPayin($this->user),
            self::TYPE_LUCKYPAY => new LuckyPayPayin($this->user),
            self::TYPE_BITZONE => new BitZonePayin($this->user),
            self::TYPE_PAYME => new PaymePayin($this->user),
            self::TYPE_PAYSCROW => new PayscrowPayin($this->user),
            self::TYPE_EASYBITS => new EasyBitsPayin($this->user),
            self::TYPE_DUALI_PAY => new DualiPayin($this->user),
            self::TYPE_NIRVANA_PAY => new NirvanaPayin($this->user),
            self::TYPE_INFINITY_PAY => new InfinityPayin($this->user),
            self::TYPE_ONEPLAT_PAY => new OnePlatPayin($this->user),
            self::TYPE_ONEVISION => new OneVisionPayin($this->user),
            self::TYPE_HIGHHELP => new HighHelpPayin($this->user),
            self::TYPE_SHADOWSPAYS => new ShadowPaysPayin($this->user),
            self::TYPE_SEQUOIAPAY => new SequoiaPayin($this->user),
            self::TYPE_EXOTIC => new ExoticPayin($this->user),
            self::TYPE_SAREX => new SarexPayin($this->user),
            self::TYPE_PSPY => new PspyPayin($this->user),
            self::TYPE_PROCTECH => new ProcTechPayin($this->user),
            self::TYPE_PAYGURU => new PayGuruPayin($this->user),
            self::TYPE_FENI => new FeniPayin($this->user),
            self::TYPE_NOROS => new NorosPayin($this->user),
            default => new UnknownPayin($this->user),
        };
    }

    /**
     * Возвращает ид (тип) системы депозита
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Возвращает название системы депозита
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Проверяет параметры платежа и возможность продолжения операции депозита
     * @param float $amount
     * @param string $method
     * @param array $data
     * @param int $method_id
     * @return bool
     */
    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $pay_system = PaymentSystems::getPaySystemById($method_id);

        $block = $this->isMultidepBlock($method, MUser::getAuth());
        if ($block !== null) {
            $this->_error = __('wallet.deposit_blocked_to', ['time' => now()->diffInSeconds($block)]) . 's';
            return false;
        }

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
        }

        if ($amount <= 0) {
            $this->_error = __('wallet.amount_should_be_more', ['amount' => 0]);
            return false;
        }

        if ($pay_system && $pay_system->getMinSum() > 0 && $amount < $pay_system->getMinSum()) {
            $this->_error = __('wallet.minimum_refill_amount', ['amount' => $pay_system->getMinSum(), 'curr' => $this->currency_name]);
            return false;
        }

        if ($pay_system && $pay_system->getMaxSum() > 0 && $amount > $pay_system->getMaxSum()) {
            $this->_error = __('wallet.max_refill_amount', ['amount' => $pay_system->getMaxSum(), 'curr' => $this->currency_name]);
            return false;
        }

        return true;
    }

    /**
     * Обрабатывает запрос депозита
     * @param $amount
     * @param $method
     * @param array $data
     * @param PayoutTransaction|null $transaction
     * @param int $method_id
     * @return \Illuminate\Http\RedirectResponse|array|bool
     * Варианты ответа: ['url' => перейти на указанный урл], ['redirect_form' => перейти формой], ['modal_data' => отобразить модальной окно]
     * @throws \Exception
     */
    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        throw new \Exception('base class method called');
    }

    /**
     * Возвращает поля реквизитов для депозита
     * @param $method
     * @param PaymentSystems|null $pay_method
     * @return string
     */
    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.default', ['method' => $method, 'pay_method' => $pay_method])->render();
    }

    /**
     * Возвращает реквизиты запроса
     * @param string $method
     * @param array $data
     * @param int $method_id
     * @return array
     */
    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        return $data;
    }

    /**
     * Заблокирован ли указанный метод депозита
     * @param $method
     * @return bool
     */
    public function isBlocked($method): bool
    {
        return false;
    }

    /**
     * Возвращает причину блокировки метода
     * @param $method
     * @return string
     */
    public function blockReason($method): string
    {
        return "";
    }

    /**
     * Возвращает массив используемых ид транзакции.
     * Необходимо для crm.
     * @return array
     */
    public function getStatusCodes(): array
    {
        return [];
    }

    /**
     * Указывает скрыта ли система для пользователя
     * @param int $currency_id
     * @param int $deposits_count
     * @param float $deposits_sum
     * @param PaymentSystems|null $system
     * @return bool
     */
    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        return false;
    }

    /**
     * Завершает транзакцию депозита
     * @param MUser $user
     * @param PayoutTransaction $transaction
     * @param float $amount
     * @param int $status
     * @param int $currency
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public static function completeTransaction(MUser $user, PayoutTransaction $transaction, float $amount,
                                               int   $status, int $currency = UserWallet::CURRENCY_RUB, ?int $dep_wager = null)
    {
        Log::channel('pay')->info('completing payin transaction');
        Log::channel('pay')->info(var_export($transaction, true));
        Localization::setAppLocale($user->getLocale());
        DB::beginTransaction();
        try {
            $active_bonus = KUserBonus::findFirstActiveBonus($user->id);
            /** @var DepositBonusInterface $deposit_bonus */

            if ($transaction->bonus_id > 0) {
                Log::channel('pay')->info('dep bonus_id: ' . $transaction->bonus_id);
                $deposit_bonus = $user->getFirstDepositBonus();

                if ($active_bonus && $active_bonus->shouldProcessPayment()) {
                    $deposit_bonus = $active_bonus;
                }

                if ($deposit_bonus !== null && !$deposit_bonus->isExpired() && $deposit_bonus->getRequiredSum() <= $amount) {
                    Log::channel('pay')->info('dep found bonus_id: ' . $transaction->bonus_id);
                    try {
                        $rest = $deposit_bonus->confirmPayment($user, $amount, $currency);
                        $transaction->bonus_id = $deposit_bonus->getId();
//                        event(new MoneySpent($user, $amount - $rest, $user->getCurrencyName()), 'bonus');
                    } catch (\Throwable $e) {
                        Hlog::logException($e);
                    }
                }
            }

            foreach (BonusFactory::getOnDepositBonuses($user) as $dep_bonus) {
                try {
                    $dep_bonus->onDeposit($amount, $currency);
                } catch (\Throwable $e) {
                    Hlog::logException($e);
                }
            }

            $transaction->status_id = $status;
            $transaction->sum = $amount;
            $transaction->status = PayoutTransaction::STATUS_SUCCESS;

            if ($transaction->save()) {
                // устанавливаем вейджер на основные
                $deposit_params = DepositParams::forCurrency($transaction->getCurrency());
                if ($deposit_params !== null) {
                    if ($dep_wager === null) $dep_wager = $deposit_params->getWager();
                    if ($user->getBalance($currency) <= $deposit_params->getWagerDropAmount()) {
                        $user->getWallet($currency)->setLockedMoney($amount * $dep_wager);
                    } else {
                        $user->getWallet($currency)->addLockedMoney($amount * $dep_wager);
                    }
                }
                $user->getWallet($currency)->addMoney($amount);

                // store deposit number
                $count = PayoutTransaction::where('user_id', $transaction->user_id)
                    ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                    ->count();
                $transaction->user_dep_n = $count;
                $transaction->save();

                //non deposit bonus percent
                /** @var NonDepositBonus $nd_bonus */
                $nd_bonus = NonDepositBonus::getDone($user->id);
                if ($nd_bonus) {
                    $nd_bonus->confirmPayment($user, $amount, $currency);
                }

                DB::commit();

                event(new MoneyDeposit($user, $amount, $currency));
                UserManager::addEvent($user->getId(), 'deposit');
                KSlc::getInstance()->dailyTasksCheckTaskByType($user->getId(), UserTaskType::MakeDeposit, Wallet::currencyName($currency));
                if ($count === 1) {
                    UserManager::addEvent($user->getId(), 'ftd');
                    NatsUserEvents::firstDep($user->getId(), $amount, $currency, $user->ref_id, $user->click_id, $user->getFingerprint());
                }
                if ($count < 4) UserManager::addEvent($user->getId(), 'dep' . $count);
                if ($count >= 4) UserManager::addEvent($user->getId(), 'dep4');

            } else {
                return response('Internal server error.', 500);
            }
        } catch (\Exception $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }
        return response('OK', 200);
    }

    /**
     * Возвращает блокировку метода по мультидепу (очень частые запросы на депозит)
     * Используется п2п системами для блокировки частых запросов с целью фальсификации платежей
     * @param string $method
     * @param MUser $user
     * @return PaymentSystemBlock|null
     */
    public function isMultidepBlock(string $method, MUser $user): ?Carbon
    {
        $pay_system = PaymentSystems::getPayinSystemForMethod($method);
        $result = KPay::getInstance()->isMultidepBlocked($user->getId(), $pay_system->getId());
        if (!$result->isSuccess()) return null;
        return $result->getResult();


        if (!$pay_system->isBlockMultideps()) return null;

        $block_time = PaymentSystemBlock::userBlockTime($user->getId(), $pay_system->getId());
        if ($block_time && $block_time->getUnblockTo() !== null && $block_time->getUnblockTo() > now()) return null;

        if ($block_time !== null && $block_time->getBlockTo() > now()) {
            return $block_time;
        }

        $period_minutes = $pay_system->getBlockMinutes();
        $dep_check_start = now()->subMinutes($period_minutes)->format('Y-m-d H:i:s');
        if ($block_time && $block_time->getUnblockTo() > $dep_check_start) $dep_check_start = $block_time->getUnblockTo();

        $last_deps = PayoutTransaction::where('user_id', $user->getId())
            ->whereIn('status_id', $this->getStatusCodes())
            ->where('date', '>=', $dep_check_start)
            ->get();

        $failed_deps = $last_deps->where('status', '!=', PayoutTransaction::STATUS_SUCCESS)->count();
        if ($failed_deps < $pay_system->getBlockAfterDeps()) return null;

        if ($block_time === null) {
            $block_time = new PaymentSystemBlock();
            $block_time->setUserId($user->getId());
            $block_time->setMethodId($pay_system->getId());
        }
        $block_time->setBlockTo(now()->addMinutes($period_minutes));
        $block_time->save();

        return $block_time;
    }

    public function confirmOtp(int $method_id, int $pay_system_id, string $otpid, string $otp_code): ResultOrErr
    {
        throw new \Exception("method don't support otp operations");
    }

    public function resendOtp(int $method_id, int $pay_system_id, string $otpid): ResultOrErr
    {
        throw new \Exception("method don't support otp operations");
    }

}
