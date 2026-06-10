<?php


namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\PaymentsAPI\BitZone\BitZone;
use App\PaymentsAPI\ExPay\ExPay;
use App\PaymentsAPI\LuckyPay\LuckyPay;
use App\PaymentsAPI\PayCraft;
use App\PaymentsAPI\Payscrow\Payscrow;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Log;

class Payout
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
    const TYPE_PAYKASSA = 25;
    const TYPE_CRYPTOMUS = 26;
    const TYPE_ALFAKIT = 27;
    const TYPE_BETATRANSFER = 28;
    const TYPE_SREDAPAY = 29;
    const TYPE_ANTAREX = 30;
    const TYPE_PAYCENTER = 31;
    const TYPE_INWIZO = 34;
    const TYPE_AEZA = 35;
    const TYPE_PAYDEX = 36;
    const TYPE_NASHOBMEN = 39;
    const TYPE_GATEEXPRESS = 40;
    const TYPE_EMPAYRE = 41;
    const TYPE_CROSSPAY = 42;
    const TYPE_CHATLPAY = 43;
    const TYPE_EXPAY = 45;
    const TYPE_PASSIMPAY = 49;
    const TYPE_PAYADMIT = 50;
    const TYPE_AIFORY = 38;
    const TYPE_BILLBLEND = 51;
    const TYPE_BESTPAY24 = 52;

    const TYPE_SKYPAY = 54;

    const TYPE_PAYONHOST = 57;

    const TYPE_REPAY = 58;

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

    protected $_error;

    public function getError(): ?string
    {
        return $this->_error;
    }

    public static function getPayoutSystem(int $system): Payout
    {
        return match ($system) {
            self::TYPE_PIASTRIX => new PiastrixAutoPayout(),
            self::TYPE_PAYEER => new PayeerAutoPayout(),
            self::TYPE_PM => new PerfectMoneyAutoPayout(),
            self::TYPE_BILLINE => new BillLineAutoPayout(),
            self::TYPE_OBMENKA => new ObmenkaAutopayout(),
            self::TYPE_SETTLEPAY => new SettleAutoPayout(),
            self::TYPE_FREEKASSA => new FreeKassaAutoPayout(),
            self::TYPE_ALIKASSA => new AliKassaAutoPayout(),
            self::TYPE_BRPAY => new BrpayAutoPayout(),
            self::TYPE_SWIFT => new SwiftAutoPayout(),
            self::TYPE_IKASSA => new InterKassaAutoPayout(),
            self::TYPE_IKINR => new IkInrAutoPayout(),
            self::TYPE_PAZE => new PazeAutoPayout(),
            self::TYPE_PAYCOS => new PaycosAutoPayout(),
            self::TYPE_PAYGATE => new PayGateAutoPayout(),
            self::TYPE_CRYPTOCLOUD => new CryptoCloudPayout(),
            self::TYPE_ALPHAPO => new AlphapoPayout(),
            self::TYPE_KVITUM => new KvitumAutoPayout(),
            self::TYPE_IKINR_PTP => new IkInrPtpAutoPayout(),
            self::TYPE_IKKZT => new IkKztAutoPayout(),
            self::TYPE_PAYLAMA => new PayLamaAutopayout(),
            self::TYPE_IKINR3 => new IkInr3AutoPayout(),
            self::TYPE_PAYKASSA => new PayKassaAutoPayout(),
            self::TYPE_CRYPTOMUS => new CryptomusAutoPayout(),
            self::TYPE_ALFAKIT => new AlfakitPayout(),
            self::TYPE_BETATRANSFER => new BetatransferPayout(),
            self::TYPE_SREDAPAY => new SredaPayPayout(),
            self::TYPE_ANTAREX => new AntarexPayout(),
            self::TYPE_PAYCENTER => new PayCenterPayout(),
            self::TYPE_ENOT => new EnotPayout(),
            self::TYPE_INWIZO => new InwizoPayout(),
            self::TYPE_AEZA => new AezaPayout(),
            self::TYPE_PAYDEX => new PaydexPayout(),
            self::TYPE_GATEEXPRESS => new GateExpressPayout(),
            self::TYPE_EMPAYRE => new EmpayrePayout(),
            self::TYPE_CROSSPAY => new CrossPayPayout(),
            self::TYPE_CHATLPAY => new ChatlPayout(),
            self::TYPE_EXPAY => new ExPayPayout(request()->get('payment-method') ?? request()->get('method')),
            self::TYPE_PASSIMPAY => new PassimpayPayout(),
            self::TYPE_PAYADMIT => new PayadmitPayout(),
            self::TYPE_AIFORY => new AiforyPayout(),
            self::TYPE_BESTPAY24 => new BestPay24Payout(),
            self::TYPE_BILLBLEND => new BillBlendPayout(),
            self::TYPE_NASHOBMEN => new NashObmenPayout(),
            self::TYPE_SKYPAY => new SkyPayAutoPayout,
            self::TYPE_PAYONHOST => new PayOnHostPayout,
            self::TYPE_REPAY => new RepayPayout,
            self::TYPE_TURCODE => new TurcodePayout,
            self::TYPE_XPAYPRO => new XPayProPayout(),
            self::TYPE_JUPITER => new JupiterPayout(),
            self::TYPE_COOCOOPAY => new CooCooPayout(),
            self::TYPE_LUCKYPAY => new LuckyPayPayout(),
            self::TYPE_BITZONE => new BitZonePayout(),
            self::TYPE_PAYME => new PaymePayout(),
            self::TYPE_PAYSCROW => new PayscrowPayout(),
            self::TYPE_EASYBITS => new EasyBitsPayout(),
            self::TYPE_DUALI_PAY => new DualiPayPayout(),
            self::TYPE_NIRVANA_PAY => new NirvanaPayPayout(),
            self::TYPE_INFINITY_PAY => new InfinityPayPayout(),
            self::TYPE_ONEPLAT_PAY => new OnePlatPayout(),
            self::TYPE_ONEVISION => new OneVisionPayout(),
            self::TYPE_HIGHHELP => new HighHelpPayout(),
            self::TYPE_SHADOWSPAYS => new ShadowPayPayout(),
            self::TYPE_SEQUOIAPAY => new SequoiaPayPayout(),
            self::TYPE_EXOTIC => new ExoticPayout(),
            self::TYPE_SAREX => new SarexPayout(),
            self::TYPE_PSPY => new PspyPayout(),
            self::TYPE_PROCTECH => new ProcTechPayout(),
            self::TYPE_PAYGURU => new PayGuruPayout(),
            self::TYPE_FENI => new FeniPayout(),
            self::TYPE_NOROS => new NorosPayout(),
            default => new UnknownPayout(),
        };
    }

    public static function getAutoPaymentSystem($pay_system): AutoPayoutSystem
    {
        if (preg_match('#^Noros\..*#', $pay_system)) {
            return new NorosPayout();
        }
        if (preg_match('#^Feni\..*#', $pay_system)) {
            return new FeniPayout();
        }
        if (preg_match('#^GuruPay\..*#', $pay_system)) {
            return new PayGuruPayout();
        }
        if (preg_match('#^ProcTech\..*#', $pay_system)) {
            return new ProctechPayout();
        }
        if (preg_match('#^Pspy\..*#', $pay_system)) {
            return new PspyPayout();
        }
        if (preg_match('#^SarexPays\..*#', $pay_system)) {
            return new SarexPayout();
        }
        if (preg_match('#^Exotic\..*#', $pay_system)) {
            return new ExoticPayout();
        }
        if (preg_match('#^SequoiaPay\..*#', $pay_system)) {
            return new SequoiaPayPayout();
        }
        if (preg_match('#^ShadowPays\..*#', $pay_system)) {
            return new ShadowPayPayout();
        }
        if (preg_match('#^HighHelp\..*#', $pay_system)) {
            return new HighHelpPayout();
        }
        if (preg_match('#^OneVision\..*#', $pay_system)) {
            return new OneVisionPayout();
        }
        if (preg_match('#^1Plat\..*#', $pay_system)) {
            return new InfinityPayPayout();
        }
        if (preg_match('#^InfinityPay\..*#', $pay_system)) {
            return new InfinityPayPayout();
        }
        if (preg_match('#^NirvanaPay\..*#', $pay_system)) {
            return new NirvanaPayPayout();
        }
        if (preg_match('#^DualiPay\..*#', $pay_system)) {
            return new DualiPayPayout();
        }
        if (preg_match('#^EasyBits\..*#', $pay_system)) {
            return new EasyBitsPayout();
        }
        if (preg_match('#^Payscrow\..*#', $pay_system)) {
            return new PayscrowPayout();
        }
        if (preg_match('#^Payme\..*#', $pay_system)) {
            return new PaymePayout();
        }
        if (preg_match('#^BitZone\..*#', $pay_system)) {
            return new BitZonePayout();
        }
        if (preg_match('#^LuckyPay\..*#', $pay_system)) {
            return new LuckyPayPayout();
        }
        if (preg_match('#^CooCooPay\..*#', $pay_system)) {
            return new CooCooPayout();
        }
        if (preg_match('#^Jupiter\..*#', $pay_system)) {
            return new JupiterPayout();
        }
        if (preg_match('#^XPayPro\..*#', $pay_system)) {
            return new XPayProPayout();
        }
        if (preg_match('#^GateExpress\..*#', $pay_system)) {
            return new GateExpressPayout();
        }
        if (preg_match('#^Empayre\..*#', $pay_system)) {
            return new EmpayrePayout();
        }
        if (preg_match('#^CrossPay\..*#', $pay_system)) {
            return new CrossPayPayout();
        }
        if (preg_match('#^Obmenka\..*#', $pay_system)) {
            return new ObmenkaAutopayout();
        }
        if (preg_match('#^Settle\..*#', $pay_system)) {
            return new SettleAutoPayout();
        }
        if (preg_match('#^FK\..*#', $pay_system)) {
            return new FreeKassaAutoPayout();
        }
        if (preg_match('#^Ali\..*#', $pay_system)) {
            return new AliKassaAutoPayout();
        }
        if (preg_match('#^Brpay\..*#', $pay_system)) {
            return new BrpayAutoPayout();
        }
        if (preg_match('#^BrPay\..*#', $pay_system)) {
            return new BrpayAutoPayout();
        }
        if (preg_match('#^Swift\..*#', $pay_system)) {
            return new SwiftAutoPayout();
        }
        if (preg_match('#^IKassa\..*#', $pay_system)) {
            return new InterKassaAutoPayout();
        }
        if (preg_match('#^IkInr\..*#', $pay_system)) {
            return new IkInrAutoPayout();
        }
        if (preg_match('#^IkInrPtp\..*#', $pay_system)) {
            return new IkInrPtpAutoPayout();
        }
        if (preg_match('#^Paze\..*#', $pay_system)) {
            return new PazeAutoPayout();
        }
        if (preg_match('#^Paycos\..*#', $pay_system)) {
            return new PaycosAutoPayout();
        }
        if (preg_match('#^PayGate\..*#', $pay_system)) {
            return new PayGateAutoPayout();
        }
        if (preg_match('#^CryptoCloud\..*#', $pay_system)) {
            return new CryptoCloudPayout();
        }
        if (preg_match('#^Alphapo\..*#', $pay_system)) {
            return new AlphapoPayout();
        }
        if (preg_match('#^billLine\..*#', $pay_system)) {
            return new BillLineAutoPayout();
        }
        if (preg_match('#^Kvitum\..*#', $pay_system)) {
            return new KvitumAutoPayout();
        }
        if (preg_match('#^IkKzt\..*#', $pay_system)) {
            return new IkKztAutoPayout();
        }
        if (preg_match('#^PayLama\..*#', $pay_system)) {
            return new PayLamaAutopayout();
        }
        if (preg_match('#^IkInr3\..*#', $pay_system)) {
            return new IkInrAutoPayout();
        }
        if (preg_match('#^Cryptomus\..*#', $pay_system)) {
            return new CryptomusAutoPayout();
        }
        if (preg_match('#^Alfakit\..*#', $pay_system)) {
            return new AlfakitPayout();
        }
        if (preg_match('#^Betatransfer\..*#', $pay_system)) {
            return new BetatransferPayout();
        }
        if (preg_match('#^SredaPay\..*#', $pay_system)) {
            return new SredaPayPayout();
        }
        if (preg_match('#^Antarex\..*#', $pay_system)) {
            return new AntarexPayout();
        }
        if (preg_match('#^PayCenter\..*#', $pay_system)) {
            return new PayCenterPayout();
        }
        if (preg_match('#^Enot\..*#', $pay_system)) {
            return new EnotPayout();
        }
        if (preg_match('#^Inwizo\..*#', $pay_system)) {
            return new InwizoPayout();
        }
        if (preg_match('#^Aeza\..*#', $pay_system)) {
            return new AezaPayout();
        }
        if (preg_match('#^Paydex\..*#', $pay_system)) {
            return new PaydexPayout();
        }
        if (preg_match('#^ChatlPay\..*#', $pay_system)) {
            return new ChatlPayout();
        }
        if (preg_match('#^ExPay\..*#', $pay_system)) {
            return new ExPayPayout(method: $pay_system);
        }
        if (preg_match('#^Passimpay\..*#', $pay_system)) {
            return new PassimpayPayout();
        }
        if (preg_match('#^Payadmit\..*#', $pay_system)) {
            return new PayadmitPayout();
        }
        if (preg_match('#^aifory\..*#', $pay_system)) {
            return new AiforyPayout();
        }
        if (preg_match('#^Aifory\..*#', $pay_system)) {
            return new AiforyPayout();
        }
        if (preg_match('#^bp24\..*#', $pay_system)) {
            return new BestPay24Payout();
        }
        if (preg_match('#^billblend\..*#', $pay_system)) {
            return new BillBlendPayout();
        }
        if (preg_match('#^NashObmen\..*#', $pay_system)) {
            return new NashObmenPayout();
        }

        if (preg_match('#^SkyPay\..*#', $pay_system)) {
            return new SkyPayAutoPayout;
        }

        if (preg_match('#^PayCraft\..*#', $pay_system)) {
            return new PayCraftPayout;
        }

        if (preg_match('#^PayOnHost\..*#', $pay_system)) {
            return new PayOnHostPayout;
        }

        if (preg_match('#^Repay\..*#', $pay_system)) {
            return new RepayPayout;
        }

        if (preg_match('#^Turcode\..*#', $pay_system)) {
            return new TurcodePayout;
        }

        switch ($pay_system) {
            case ApplicationForPayment::TYPE_PAYEER:
                return new PayeerAutoPayout();
            case ApplicationForPayment::TYPE_PERFECT_MONEY:
                return new PerfectMoneyAutoPayout();
            case ApplicationForPayment::TYPE_BILL_LINE:
                return new BillLineAutoPayout();
            default:
                return new PiastrixAutoPayout();
        }
    }

    /**
     * @param float $amount
     * @param string $method
     * @return bool
     */
    public function canProceed(float $amount, string $method, Wallet $wallet): bool
    {
        Log::channel('payout')->info($amount);
        $pay_system = PaymentSystems::getPayoutSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
        }

        if ($pay_system && $pay_system->min_sum > 0 && $amount < $pay_system->min_sum) {
            $this->_error = __('wallet.minimum_payout_amount', ['amount' => $pay_system->min_sum, 'curr' => $wallet->getCurrencyName()]);
            return false;
        }

        if ($pay_system && $pay_system->max_sum > 0 && $amount > $pay_system->max_sum) {
            $this->_error = __('wallet.max_payout_amount', ['amount' => $pay_system->max_sum, 'curr' => $wallet->getCurrencyName()]);
            return false;
        }

        // Ensure that user has enough money.
        if ($wallet->getBalance() < $amount) {
            $this->_error = __('wallet.not_enough_money');
            return false;
        }

        return true;
    }

    public function process($amount, $method): bool
    {
        $this->_error = 'base class method called';
        return false;
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.default', ['method' => $method, 'pay_method' => $pay_method]);
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        //clear spaces in the card number
        if (!empty($data['cardNumber'])) {
            $data['cardNumber'] = preg_replace('#\s#', '', $data['cardNumber']);
        }
        return $data;
    }

    public function isBlocked($method): bool
    {
        return false;
    }

    public function blockReason($method): string
    {
        return "";
    }

    public function checkParams(array $params): bool
    {
        return true;
    }
}
