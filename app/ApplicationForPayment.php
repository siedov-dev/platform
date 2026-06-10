<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApplicationForPayment
 * Заявка на выплату.
 *
 * @property int id
 * @property int hall_id
 * @property int partner_id
 * @property int user_id
 * @property string phone
 * @property string FIO
 * @property string card_number
 * @property float suma
 * @property string system_pay
 * @property string token
 * @property int confirmed
 * @property string comentar
 * @property string date
 * @property float money
 * @property int piastrix_id
 * @property string error
 * @property int currency
 * @property string requisites
 * @property string outer_id
 */
class ApplicationForPayment extends Model
{

    const STATUS_PENDING = 0;
    const STATUS_DONE = 1;
    const STATUS_REFUSED = 2;
    const STATUS_CANCELED = 3;
    const STATUS_AUTO_PAY_PENDING = 4;
    const STATUS_AUTO_PAY_PROCESS = 5;
    const STATUS_AUTO_PAY_SUCCESS = 6;
    const STATUS_AUTO_PAY_ERROR = 7;

    const TYPE_VISA_RUB = 'Visa RUB';
    const TYPE_VISA_UAH = 'Visa UAH';
    const TYPE_YANDEX = 'Yandex.Money';
    const TYPE_PRIVAT_24 = 'Privat24';
    const TYPE_QIWI = 'Qiwi';
    const TYPE_QIWI_VISA = 'Qiwi.Visa';//disabled
    const TYPE_PAYEER = 'payeer';
    const TYPE_PERFECT_MONEY = 'perfectmoney';
    const TYPE_BILL_LINE = 'billLine';
    const TYPE_PIASTRIX_RUB = 'piastrix_rub';

    protected $table = 'application_for_payment';
    public $timestamps = false;

    protected $hidden = [
        'card_number',
        'token',
        'requisites',
    ];

    public static function getStatusName(int $status_code):string
    {
        return match ($status_code) {
            self::STATUS_PENDING => __('cabinet.pending'),
            self::STATUS_DONE => __('cabinet.success'),
            self::STATUS_CANCELED => __('cabinet.canceled'),
            self::STATUS_AUTO_PAY_PENDING => __('cabinet.pending'),
            self::STATUS_AUTO_PAY_PROCESS => __('cabinet.process'),
            self::STATUS_AUTO_PAY_SUCCESS => __('cabinet.success'),
            self::STATUS_AUTO_PAY_ERROR => __('cabinet.error'),
            default => 'unknown',
        };
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCardNumber(): string
    {
        return $this->card_number;
    }

    public function getSystemPay(): string
    {
        return $this->system_pay;
    }

    public function getConfirmed(): int
    {
        return $this->confirmed;
    }

    public function getPiastrixId(): int
    {
        return $this->piastrix_id;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function getRequisites(): string
    {
        return $this->requisites;
    }

    public function getOuterId(): string
    {
        return $this->outer_id;
    }
}
