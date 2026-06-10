<?php

namespace App;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Models\Pay3dsData;
use App\Models\UserMoneyStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class PayoutTransaction
 * @package App
 * @property int id
 * @property int user_id
 * @property int hall_id
 * @property string date
 * @property float sum
 * @property float fee
 * @property float total_sum
 * @property string currency
 * @property string operation
 * @property string card_number
 * @property int status_id
 * @property int error_code_id
 * @property int last_changes_agent_id
 * @property string last_changes_time
 * @property int type_transaction_id
 * @property string type_payout_id
 * @property string pay_attrs
 * @property string coment
 * @property bool use_bonus
 * @property int $system_id
 * @property string $method
 * @property int $bonus_id
 * @property string $bonus_system
 * @property string $o_tr_id
 * @property int $type
 * @property int $status
 * @property int $user_dep_n
 * @property string $created_on
 * @property int $method_id
 */
class PayoutTransaction extends Model
{
    protected $table = 'payout_transactions';
    public $timestamps = false;

    protected $hidden = [
        'card_number',
        'pay_attrs',
    ];

    const STATUS_PENDING = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_3DS = 4;
    const STATUS_3DS_REDIRECT = 5;

    const TYPE_PAYIN = 0;
    const TYPE_PAYOUT = 1;

    const STATUS_SLC_EXCHANGE = 15;
    const STATUS_CRM_PAYMENT = 20;
    const STATUS_BONUS_ACTIVATED = 35;

    const STATUS_QT_FS = 39;
    const STATUS_QT_TRANSFER = 40;
    const STATUS_QT_RETURN = 41;
    const STATUS_SOFTSWISS_DEPOSIT = 42;
    const STATUS_SOFTSWISS_RETURN = 43;

    const STATUS_TVBET_PROMO = 50;
    const STATUS_TVBET_BET = 51;
    const STATUS_TVBET_WIN = 52;
    const STATUS_TVBET_REFUND = 53;
    const STATUS_TVBET_JACKPOT = 54;

    const STATUS_PIASTRIX_PENDING = 59;
    const STATUS_PIASTRIX_FAIL = 66;
    const STATUS_PIASTRIX_ERROR = 46;


    const STATUS_INVEST_PAYMENT = 6;
    const STATUS_INVEST_PAYOUT = 7;

    // Incoming statuses.
    const PAID_IN_VISA = 60;
    const PAID_IN_PAYEER = 102;
    const STATUS_PIASTRIX_SUCCESS = 65;

    // Outgoing statuses.
    const PAID_OUT_XFANBET = 28;
    const PAID_OUT_SPORT_XFANBET = 31;
    const PAID_OUT_VISA = 45;
    const STATUS_PIASTRIX_GI = 113;
    const STATUS_PIASTRIX_GI_SUCCESS = 114;
    const STATUS_PIASTRIX_GI_FAIL = 115;

    const STATUS_ENOT_PENDING = 121;
    const STATUS_ENOT_SUCCESS = 122;
    const STATUS_ENOT_FAIL = 123;

    const STATUS_PM_PENDING = 125;
    const STATUS_PM_SUCCESS = 126;
    const STATUS_PM_FAIL = 127;

    const STATUS_BL_PENDING = 128;
    const STATUS_BL_SUCCESS = 129;
    const STATUS_BL_FAIL = 130;

    const STATUS_OBMENKA_PENDING = 131;
    const STATUS_OBMENKA_SUCCESS = 132;
    const STATUS_OBMENKA_FAIL = 133;

    const STATUS_SETTLE_PENDING = 134;
    const STATUS_SETTLE_SUCCESS = 135;
    const STATUS_SETTLE_FAIL = 136;
    const STATUS_SETTLE_PAYOUT = 137;

    const STATUS_FK_PENDING = 138;
    const STATUS_FK_SUCCESS = 139;
    const STATUS_FK_FAIL = 140;

    const STATUS_ALI_PENDING = 141;
    const STATUS_ALI_SUCCESS = 142;
    const STATUS_ALI_FAIL = 143;
    const STATUS_ALI_PAYOUT = 144;

    const STATUS_BRPAY_PENDING = 145;
    const STATUS_BRPAY_SUCCESS = 146;
    const STATUS_BRPAY_FAIL = 147;

    const STATUS_SWIFT_PENDING = 148;
    const STATUS_SWIFT_SUCCESS = 149;
    const STATUS_SWIFT_FAIL = 150;

    const STATUS_IK_PENDING = 151;
    const STATUS_IK_SUCCESS = 152;
    const STATUS_IK_FAIL = 153;

    const STATUS_REFERRAL_PAY = 154;

    const STATUS_TOURNAMENT_BUY = 155;
    const STATUS_TOURNAMENT_WIN = 156;

    const STATUS_PAZE_PENDING = 157;
    const STATUS_PAZE_SUCCESS = 158;
    const STATUS_PAZE_FAIL = 159;

    const STATUS_PAYCOS_PENDING = 160;
    const STATUS_PAYCOS_SUCCESS = 161;
    const STATUS_PAYCOS_FAIL = 162;

    const STATUS_PAYGATE_PENDING = 163;
    const STATUS_PAYGATE_SUCCESS = 164;
    const STATUS_PAYGATE_FAIL = 165;

    const STATUS_CRYPTOCLOUD_PENDING = 166;
    const STATUS_CRYPTOCLOUD_SUCCESS = 167;
    const STATUS_CRYPTOCLOUD_FAIL = 168;

    const STATUS_ALPHAPO_PENDING = 169;
    const STATUS_ALPHAPO_SUCCESS = 170;
    const STATUS_ALPHAPO_FAIL = 171;

    const STATUS_KVITUM_PENDING = 172;
    const STATUS_KVITUM_SUCCESS = 173;
    const STATUS_KVITUM_FAIL = 174;

    const STATUS_PAYLAMA_PENDING = 175;
    const STATUS_PAYLAMA_SUCCESS = 176;
    const STATUS_PAYLAMA_FAIL = 177;

    const STATUS_EINPAYZ_PENDING = 178;
    const STATUS_EINPAYZ_SUCCESS = 179;
    const STATUS_EINPAYZ_FAIL = 180;

    const STATUS_PAYKASSA_PENDING = 181;
    const STATUS_PAYKASSA_SUCCESS = 182;
    const STATUS_PAYKASSA_FAIL = 183;

    const STATUS_CRYPTOMUS_PENDING = 184;
    const STATUS_CRYPTOMUS_SUCCESS = 185;
    const STATUS_CRYPTOMUS_FAIL = 186;

    const STATUS_ALFAKIT_PENDING = 187;
    const STATUS_ALFAKIT_SUCCESS = 188;
    const STATUS_ALFAKIT_FAIL = 189;

    const STATUS_BETATRANSFER_PENDING = 190;
    const STATUS_BETATRANSFER_SUCCESS = 191;
    const STATUS_BETATRANSFER_FAIL = 192;

    const STATUS_SREDAPAY_PENDING = 193;
    const STATUS_SREDAPAY_SUCCESS = 194;
    const STATUS_SREDAPAY_FAIL = 195;

    const STATUS_ANTAREX_PENDING = 196;
    const STATUS_ANTAREX_SUCCESS = 197;
    const STATUS_ANTAREX_FAIL = 198;

    const STATUS_PAYCENTER_PENDING = 199;
    const STATUS_PAYCENTER_SUCCESS = 200;
    const STATUS_PAYCENTER_FAIL = 201;

    const STATUS_B2PAY_PENDING = 202;
    const STATUS_B2PAY_SUCCESS = 203;
    const STATUS_B2PAY_FAIL = 204;

    const STATUS_POSTMAN_PENDING = 205;
    const STATUS_POSTMAN_SUCCESS = 206;
    const STATUS_POSTMAN_FAIL = 207;

    const STATUS_BETPROD_BET = 208;
    const STATUS_BETPROD_WIN = 209;
    const STATUS_BETPROD_REFUND = 210;

    const STATUS_INWIZO_PENDING = 211;
    const STATUS_INWIZO_SUCCESS = 212;
    const STATUS_INWIZO_FAIL = 213;

    const STATUS_AEZA_PENDING = 214;
    const STATUS_AEZA_SUCCESS = 215;
    const STATUS_AEZA_FAIL = 216;

    const STATUS_PAYDEX_PENDING = 217;
    const STATUS_PAYDEX_SUCCESS = 218;
    const STATUS_PAYDEX_FAIL = 219;

    const STATUS_SANDBOX_PENDING = 220;
    const STATUS_SANDBOX_SUCCESS = 221;
    const STATUS_SANDBOX_FAIL = 222;

    const STATUS_AIFORY_PENDING = 223;
    const STATUS_AIFORY_SUCCESS = 224;
    const STATUS_AIFORY_FAIL = 225;

    const STATUS_NASHOBMEN_PENDING = 226;
    const STATUS_NASHOBMEN_SUCCESS = 227;
    const STATUS_NASHOBMEN_FAIL = 228;

    const STATUS_GATEEXPRESS_PENDING = 229;
    const STATUS_GATEEXPRESS_SUCCESS = 230;
    const STATUS_GATEEXPRESS_FAIL = 231;

    const STATUS_EMPAYRE_PENDING = 232;
    const STATUS_EMPAYRE_SUCCESS = 233;
    const STATUS_EMPAYRE_FAIL = 234;

    const STATUS_CROSSPAY_PENDING = 235;
    const STATUS_CROSSPAY_SUCCESS = 236;
    const STATUS_CROSSPAY_FAIL = 237;

    const STATUS_CHATLPAY_PENDING = 238;
    const STATUS_CHATLPAY_SUCCESS = 239;
    const STATUS_CHATLPAY_FAIL = 240;

    const STATUS_PAY4X_PENDING = 241;
    const STATUS_PAY4X_SUCCESS = 242;
    const STATUS_PAY4X_FAIL = 243;

    const STATUS_EXPAY_PENDING = 244;
    const STATUS_EXPAY_SUCCESS = 245;
    const STATUS_EXPAY_FAIL = 246;

    const STATUS_PAYCROWN_PENDING = 247;
    const STATUS_PAYCROWN_SUCCESS = 248;
    const STATUS_PAYCROWN_FAIL = 249;

    const STATUS_PASSIMPAY_PENDING = 250;
    const STATUS_PASSIMPAY_SUCCESS = 251;
    const STATUS_PASSIMPAY_FAIL = 252;

    const STATUS_PAYADMIT_PENDING = 253;
    const STATUS_PAYADMIT_SUCCESS = 254;
    const STATUS_PAYADMIT_FAIL = 255;

    const STATUS_ANYPAY_PENDING = 256;
    const STATUS_ANYPAY_SUCCESS = 257;
    const STATUS_ANYPAY_FAIL = 258;

    const STATUS_BILLBLEND_PENDING = 259;
    const STATUS_BILLBLEND_SUCCESS = 260;
    const STATUS_BILLBLEND_FAIL = 261;

    const STATUS_BESTPAY24_PENDING = 262;
    const STATUS_BESTPAY24_SUCCESS = 263;
    const STATUS_BESTPAY24_FAIL = 264;

    const STATUS_CHASSE_PENDING = 265;
    const STATUS_CHASSE_SUCCESS = 266;
    const STATUS_CHASSE_FAIL = 267;

    const STATUS_SKYPAY_PENDING = 270;
    const STATUS_SKYPAY_SUCCESS = 271;
    const STATUS_SKYPAY_FAIL = 272;

    const STATUS_MAYFPAY_PENDING = 273;
    const STATUS_MAYFPAY_SUCCESS = 274;
    const STATUS_MAYFPAY_FAIL = 275;

    const STATUS_PAYCRAFT_PENDING = 280;
    const STATUS_PAYCRAFT_SUCCESS = 281;
    const STATUS_PAYCRAFT_FAIL = 282;

    const STATUS_XPAYPRO_PENDING = 283;
    const STATUS_XPAYPRO_SUCCESS = 284;
    const STATUS_XPAYPRO_FAIL = 285;

    const STATUS_COOCOO_PENDING = 286;
    const STATUS_COOCOO_SUCCESS = 287;
    const STATUS_COOCOO_FAIL = 288;

    const STATUS_LUCKYPAY_PENDING = 289;
    const STATUS_LUCKYPAY_SUCCESS = 290;
    const STATUS_LUCKYPAY_FAIL = 291;

    const STATUS_BITZONE_PENDING = 292;
    const STATUS_BITZONE_SUCCESS = 293;
    const STATUS_BITZONE_FAIL = 294;

    const STATUS_PAYME_PENDING = 295;
    const STATUS_PAYME_SUCCESS = 296;
    const STATUS_PAYME_FAIL = 297;

    const STATUS_PAYSCROW_PENDING = 298;
    const STATUS_PAYSCROW_SUCCESS = 299;
    const STATUS_PAYSCROW_FAIL = 300;

    const STATUS_EASYBITS_PENDING = 301;
    const STATUS_EASYBITS_SUCCESS = 302;
    const STATUS_EASYBITS_FAIL = 303;

    const STATUS_DUALIPAY_PENDING = 304;
    const STATUS_DUALIPAY_SUCCESS = 305;
    const STATUS_DUALIPAY_FAIL = 306;

    const STATUS_NIRVANA_PENDING = 307;
    const STATUS_NIRVANA_SUCCESS = 308;
    const STATUS_NIRVANA_FAIL = 309;

    const STATUS_INFINITY_PENDING = 310;
    const STATUS_INFINITY_SUCCESS = 311;
    const STATUS_INFINITY_FAIL = 312;

    const STATUS_ONEPLAT_PENDING = 313;
    const STATUS_ONEPLAT_SUCCESS = 314;
    const STATUS_ONEPLAT_FAIL = 315;

    const STATUS_ONEVISION_PENDING = 316;
    const STATUS_ONEVISION_SUCCESS = 317;
    const STATUS_ONEVISION_FAIL = 318;

    const STATUS_HIGHHELP_PENDING = 319;
    const STATUS_HIGHHELP_SUCCESS = 320;
    const STATUS_HIGHHELP_FAIL = 321;

    const STATUS_SHADOWPAYS_PENDING = 322;
    const STATUS_SHADOWPAYS_SUCCESS = 323;
    const STATUS_SHADOWPAYS_FAIL = 324;

    const STATUS_SEQUOIA_PENDING = 325;
    const STATUS_SEQUOIA_SUCCESS = 326;
    const STATUS_SEQUOIA_FAIL = 327;

    const STATUS_EXOTIC_PENDING = 328;
    const STATUS_EXOTIC_SUCCESS = 329;
    const STATUS_EXOTIC_FAIL = 330;

    const STATUS_SAREX_PENDING = 331;
    const STATUS_SAREX_SUCCESS = 332;
    const STATUS_SAREX_FAIL = 333;

    const STATUS_PSPY_PENDING = 334;
    const STATUS_PSPY_SUCCESS = 335;
    const STATUS_PSPY_FAIL = 336;

    const STATUS_PROCTECH_PENDING = 337;
    const STATUS_PROCTECH_SUCCESS = 338;
    const STATUS_PROCTECH_FAIL = 339;

    const STATUS_PAYGURU_PENDING = 340;
    const STATUS_PAYGURU_SUCCESS = 341;
    const STATUS_PAYGURU_FAIL = 342;

    const STATUS_FENI_PENDING = 343;
    const STATUS_FENI_SUCCESS = 344;
    const STATUS_FENI_FAIL = 345;

    const STATUS_NOROS_PENDING = 346;
    const STATUS_NOROS_SUCCESS = 347;
    const STATUS_NOROS_FAIL = 348;

    const STATUS_PAYONHOST_PENDING = 570;
    const STATUS_PAYONHOST_SUCCESS = 571;
    const STATUS_PAYONHOST_FAIL = 572;

    const STATUS_REPAY_PENDING = 573;
    const STATUS_REPAY_SUCCESS = 574;
    const STATUS_REPAY_FAIL = 575;

    const STATUS_STREAMPAY_PENDING = 576;
    const STATUS_STREAMPAY_SUCCESS = 577;
    const STATUS_STREAMPAY_FAIL = 578;

    const STATUS_TURCODE_PENDING = 580;
    const STATUS_TURCODE_SUCCESS = 581;
    const STATUS_TURCODE_FAIL = 582;

    const STATUS_JUPITER_PENDING = 583;
    const STATUS_JUPITER_SUCCESS = 584;
    const STATUS_JUPITER_FAIL = 585;

    public static function getIncomingStatuses()
    {
        return [
            self::PAID_IN_VISA,
            self::STATUS_PIASTRIX_SUCCESS,
            self::PAID_IN_PAYEER,
            self::STATUS_ENOT_SUCCESS,
            self::STATUS_PM_SUCCESS,
            self::STATUS_BL_SUCCESS,
            self::STATUS_OBMENKA_SUCCESS,
            self::STATUS_SETTLE_SUCCESS,
            self::STATUS_FK_SUCCESS,
            self::STATUS_ALI_SUCCESS,
            self::STATUS_BRPAY_SUCCESS,
            self::STATUS_SWIFT_SUCCESS,
            self::STATUS_IK_SUCCESS,
            self::STATUS_PAZE_SUCCESS,
            self::STATUS_PAYCOS_SUCCESS,
            self::STATUS_PAYGATE_SUCCESS,
            self::STATUS_CRYPTOCLOUD_SUCCESS,
            self::STATUS_ALPHAPO_SUCCESS,
            self::STATUS_KVITUM_SUCCESS,
            self::STATUS_PAYLAMA_SUCCESS,
            self::STATUS_EINPAYZ_SUCCESS,
            self::STATUS_PAYKASSA_SUCCESS,
            self::STATUS_CRYPTOMUS_SUCCESS,
            self::STATUS_ALFAKIT_SUCCESS,
            self::STATUS_BETATRANSFER_SUCCESS,
            self::STATUS_SREDAPAY_SUCCESS,
            self::STATUS_ANTAREX_SUCCESS,
            self::STATUS_PAYCENTER_SUCCESS,
            self::STATUS_B2PAY_SUCCESS,
            self::STATUS_POSTMAN_SUCCESS,
            self::STATUS_INWIZO_SUCCESS,
            self::STATUS_AEZA_SUCCESS,
            self::STATUS_PAYDEX_SUCCESS,
            self::STATUS_SANDBOX_SUCCESS,
            self::STATUS_AIFORY_SUCCESS,
            self::STATUS_NASHOBMEN_SUCCESS,
            self::STATUS_GATEEXPRESS_SUCCESS,
            self::STATUS_EMPAYRE_SUCCESS,
            self::STATUS_CROSSPAY_SUCCESS,
            self::STATUS_CHATLPAY_SUCCESS,
            self::STATUS_PAY4X_SUCCESS,
            self::STATUS_EXPAY_SUCCESS,
            self::STATUS_PAYCROWN_SUCCESS,
            self::STATUS_PASSIMPAY_SUCCESS,
            self::STATUS_PAYADMIT_SUCCESS,
            self::STATUS_ANYPAY_SUCCESS,
            self::STATUS_BILLBLEND_SUCCESS,
            self::STATUS_BESTPAY24_SUCCESS,
            self::STATUS_CHASSE_SUCCESS,
            self::STATUS_SUCCESS,
            self::STATUS_SKYPAY_SUCCESS,
            self::STATUS_MAYFPAY_SUCCESS,
            self::STATUS_PAYCRAFT_SUCCESS,
            self::STATUS_PAYONHOST_SUCCESS,
            self::STATUS_REPAY_SUCCESS,
            self::STATUS_STREAMPAY_SUCCESS,
            self::STATUS_TURCODE_SUCCESS,
            self::STATUS_XPAYPRO_SUCCESS,
            self::STATUS_JUPITER_SUCCESS,
            self::STATUS_COOCOO_SUCCESS,
            self::STATUS_LUCKYPAY_SUCCESS,
            self::STATUS_BITZONE_SUCCESS,
            self::STATUS_PAYME_SUCCESS,
            self::STATUS_PAYSCROW_SUCCESS,
            self::STATUS_EASYBITS_SUCCESS,
            self::STATUS_DUALIPAY_SUCCESS,
            self::STATUS_NIRVANA_SUCCESS,
            self::STATUS_INFINITY_SUCCESS,
            self::STATUS_ONEPLAT_SUCCESS,
            self::STATUS_ONEVISION_SUCCESS,
            self::STATUS_HIGHHELP_SUCCESS,
            self::STATUS_SHADOWPAYS_SUCCESS,
            self::STATUS_SEQUOIA_SUCCESS,
            self::STATUS_EXOTIC_SUCCESS,
            self::STATUS_SAREX_SUCCESS,
            self::STATUS_PSPY_SUCCESS,
            self::STATUS_PROCTECH_SUCCESS,
            self::STATUS_PAYGURU_SUCCESS,
            self::STATUS_FENI_SUCCESS,
            self::STATUS_NOROS_SUCCESS,
        ];
    }


    public static function getOutgoingStatuses()
    {
        return [
            self::PAID_OUT_VISA,
        ];
    }

    public static function getPendingStatuses()
    {
        return [
            self::STATUS_PIASTRIX_PENDING,
            self::STATUS_PIASTRIX_GI,
            self::STATUS_ENOT_PENDING,
            self::STATUS_PM_PENDING,
            self::STATUS_BL_PENDING,
            self::STATUS_OBMENKA_PENDING,
            self::STATUS_SETTLE_PENDING,
            self::STATUS_FK_PENDING,
            self::STATUS_ALI_PENDING,
            self::STATUS_BRPAY_PENDING,
            self::STATUS_SWIFT_PENDING,
            self::STATUS_IK_PENDING,
            self::STATUS_PAZE_PENDING,
            self::STATUS_PAYCOS_PENDING,
            self::STATUS_PAYGATE_PENDING,
            self::STATUS_CRYPTOCLOUD_PENDING,
            self::STATUS_ALPHAPO_PENDING,
            self::STATUS_KVITUM_PENDING,
            self::STATUS_PAYLAMA_PENDING,
            self::STATUS_EINPAYZ_PENDING,
            self::STATUS_PAYKASSA_PENDING,
            self::STATUS_CRYPTOMUS_PENDING,
            self::STATUS_ALFAKIT_PENDING,
            self::STATUS_BETATRANSFER_PENDING,
            self::STATUS_SREDAPAY_PENDING,
            self::STATUS_ANTAREX_PENDING,
            self::STATUS_PAYCENTER_PENDING,
            self::STATUS_B2PAY_PENDING,
            self::STATUS_POSTMAN_PENDING,
            self::STATUS_INWIZO_PENDING,
            self::STATUS_AEZA_PENDING,
            self::STATUS_PAYDEX_PENDING,
            self::STATUS_SANDBOX_PENDING,
            self::STATUS_AIFORY_PENDING,
            self::STATUS_NASHOBMEN_PENDING,
            self::STATUS_GATEEXPRESS_PENDING,
            self::STATUS_EMPAYRE_PENDING,
            self::STATUS_CROSSPAY_PENDING,
            self::STATUS_CHATLPAY_PENDING,
            self::STATUS_PAY4X_PENDING,
            self::STATUS_EXPAY_PENDING,
            self::STATUS_PAYCROWN_PENDING,
            self::STATUS_PASSIMPAY_PENDING,
            self::STATUS_PAYADMIT_PENDING,
            self::STATUS_ANYPAY_PENDING,
            self::STATUS_BILLBLEND_PENDING,
            self::STATUS_BESTPAY24_PENDING,
            self::STATUS_CHASSE_PENDING,
            self::STATUS_PENDING,
            self::STATUS_SKYPAY_PENDING,
            self::STATUS_MAYFPAY_PENDING,
            self::STATUS_PAYCRAFT_PENDING,
            self::STATUS_PAYONHOST_PENDING,
            self::STATUS_REPAY_PENDING,
            self::STATUS_STREAMPAY_PENDING,
            self::STATUS_TURCODE_PENDING,
            self::STATUS_XPAYPRO_PENDING,
            self::STATUS_JUPITER_PENDING,
            self::STATUS_COOCOO_PENDING,
            self::STATUS_LUCKYPAY_PENDING,
            self::STATUS_BITZONE_PENDING,
            self::STATUS_PAYME_PENDING,
            self::STATUS_PAYSCROW_PENDING,
            self::STATUS_EASYBITS_PENDING,
            self::STATUS_DUALIPAY_PENDING,
            self::STATUS_NIRVANA_PENDING,
            self::STATUS_INFINITY_PENDING,
            self::STATUS_ONEPLAT_PENDING,
            self::STATUS_ONEVISION_PENDING,
            self::STATUS_HIGHHELP_PENDING,
            self::STATUS_SHADOWPAYS_PENDING,
            self::STATUS_SEQUOIA_PENDING,
            self::STATUS_EXOTIC_PENDING,
            self::STATUS_SAREX_PENDING,
            self::STATUS_PSPY_PENDING,
            self::STATUS_PROCTECH_PENDING,
            self::STATUS_PAYGURU_PENDING,
            self::STATUS_FENI_PENDING,
            self::STATUS_NOROS_PENDING,
        ];
    }

    public static function getFailedStatuses()
    {
        return [
            self::STATUS_PIASTRIX_FAIL,
            self::STATUS_PIASTRIX_GI_FAIL,
            self::STATUS_ENOT_FAIL,
            self::STATUS_PM_FAIL,
            self::STATUS_BL_FAIL,
            self::STATUS_OBMENKA_FAIL,
            self::STATUS_SETTLE_FAIL,
            self::STATUS_FK_FAIL,
            self::STATUS_ALI_FAIL,
            self::STATUS_BRPAY_FAIL,
            self::STATUS_SWIFT_FAIL,
            self::STATUS_IK_FAIL,
            self::STATUS_PIASTRIX_ERROR,
            self::STATUS_PAZE_FAIL,
            self::STATUS_PAYCOS_FAIL,
            self::STATUS_PAYGATE_FAIL,
            self::STATUS_CRYPTOCLOUD_FAIL,
            self::STATUS_ALPHAPO_FAIL,
            self::STATUS_KVITUM_FAIL,
            self::STATUS_PAYLAMA_FAIL,
            self::STATUS_EINPAYZ_FAIL,
            self::STATUS_PAYKASSA_FAIL,
            self::STATUS_CRYPTOMUS_FAIL,
            self::STATUS_ALFAKIT_FAIL,
            self::STATUS_BETATRANSFER_FAIL,
            self::STATUS_SREDAPAY_FAIL,
            self::STATUS_ANTAREX_FAIL,
            self::STATUS_PAYCENTER_FAIL,
            self::STATUS_B2PAY_FAIL,
            self::STATUS_POSTMAN_FAIL,
            self::STATUS_INWIZO_FAIL,
            self::STATUS_AEZA_FAIL,
            self::STATUS_PAYDEX_FAIL,
            self::STATUS_SANDBOX_FAIL,
            self::STATUS_AIFORY_FAIL,
            self::STATUS_NASHOBMEN_FAIL,
            self::STATUS_GATEEXPRESS_FAIL,
            self::STATUS_EMPAYRE_FAIL,
            self::STATUS_CROSSPAY_FAIL,
            self::STATUS_CHATLPAY_FAIL,
            self::STATUS_PAY4X_FAIL,
            self::STATUS_EXPAY_FAIL,
            self::STATUS_PAYCROWN_FAIL,
            self::STATUS_PASSIMPAY_FAIL,
            self::STATUS_PAYADMIT_FAIL,
            self::STATUS_ANYPAY_FAIL,
            self::STATUS_BILLBLEND_FAIL,
            self::STATUS_BESTPAY24_FAIL,
            self::STATUS_CHASSE_FAIL,
            self::STATUS_ERROR,
            self::STATUS_SKYPAY_FAIL,
            self::STATUS_MAYFPAY_FAIL,
            self::STATUS_PAYCRAFT_FAIL,
            self::STATUS_PAYONHOST_FAIL,
            self::STATUS_REPAY_FAIL,
            self::STATUS_STREAMPAY_FAIL,
            self::STATUS_TURCODE_FAIL,
            self::STATUS_EXPAY_FAIL,
            self::STATUS_JUPITER_FAIL,
            self::STATUS_COOCOO_FAIL,
            self::STATUS_LUCKYPAY_FAIL,
            self::STATUS_BITZONE_FAIL,
            self::STATUS_PAYME_FAIL,
            self::STATUS_XPAYPRO_FAIL,
            self::STATUS_PAYSCROW_FAIL,
            self::STATUS_EASYBITS_FAIL,
            self::STATUS_DUALIPAY_FAIL,
            self::STATUS_NIRVANA_FAIL,
            self::STATUS_INFINITY_FAIL,
            self::STATUS_ONEPLAT_FAIL,
            self::STATUS_ONEVISION_FAIL,
            self::STATUS_HIGHHELP_FAIL,
            self::STATUS_SHADOWPAYS_FAIL,
            self::STATUS_SEQUOIA_FAIL,
            self::STATUS_EXOTIC_FAIL,
            self::STATUS_SAREX_FAIL,
            self::STATUS_PSPY_FAIL,
            self::STATUS_PROCTECH_FAIL,
            self::STATUS_PAYGURU_FAIL,
            self::STATUS_FENI_FAIL,
            self::STATUS_NOROS_FAIL,
        ];
    }

    protected $fillable = ['user_id', 'hall_id', 'sum', 'currency', 'operation', 'status_id', 'date'];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getSum(): float
    {
        return $this->sum;
    }

    public function getFee(): float
    {
        return $this->fee;
    }

    public function getTotalSum(): float
    {
        return $this->total_sum;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCardNumber(): string
    {
        return $this->card_number;
    }

    public function getStatusId(): int
    {
        return $this->status_id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getOTrId(): string
    {
        return $this->o_tr_id;
    }

    public function get3dsData(): ?Pay3dsData
    {
        return Pay3dsData::where('transaction_id', $this->id)->first();
    }

    public function getCreatedOn(): ?string
    {
        return $this->created_on;
    }

    public function setCreatedOn(string $created_on): void
    {
        $this->created_on = $created_on;
    }

    public static function booted()
    {
        static::updated(function (PayoutTransaction $payoutTransaction) {
            Log::info("payout transaction updated: " . $payoutTransaction->id);
            if (in_array($payoutTransaction->status_id, PayoutTransaction::getFailedStatuses())) {
                try {
                    $user = MUser::find($payoutTransaction->user_id);
                    if (!$user) {
                        Log::error('User not found for payout transaction id: ' . $payoutTransaction->id);
                        return;
                    }
                    $moneyStat = UserMoneyStat::getStat($user, Wallet::getCurrencyCode($payoutTransaction->currency), 'total');
                    Log::info("moneyStat->deposit_count ".$moneyStat->deposit_count);
                    if ($moneyStat->deposit_count == 0) {
                        Log::info("sending FdFail customer event");
                        CustomerIo::getInstance()->sendEvent($payoutTransaction->user_id, CioEventType::FdFail, [
                            'amount' => $payoutTransaction->sum,
                            'currency' => $payoutTransaction->currency,
                        ]);
                    }
                } catch (\Throwable $e) {
                    HLog::error($e);
                }
            }
        });
    }
}

/*
очистить пользователей
select m_users.* FROM `m_users`
WHERE created_at <= '2021-01-01'
and ref_id is null
and m_users.id not in (
SELECT user_id
FROM `user_referrals`
)
and m_users.id not in (
    select DISTINCT user_id
    from payout_transactions
    where payout_transactions.status_id in (
     45,
     60,
     65,
     102,
     122,
     126,
     129,
     132,
     135,
     139,
     142,
     146,
     149,
     152,
     158,
     161,
     164,
     167,
     170,
     173,
     176,
    179,
    182
    )
    and user_id is not null
)

пендинг транзакции

SELECT * FROM `payout_transactions`
where date < '2022-01-01'
and status_id in (
     59,
     113,
     121,
     125,
     128,
     131,
     134,
     138,
     141,
     145,
     148,
     151,
     157,
     160,
     163,
     166,
     169,
     172,
     175,
    178,
    181
)
 */
