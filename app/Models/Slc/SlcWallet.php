<?php

namespace App\Models\Slc;

use App\Services\KSlc\KSlc;

/**
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $currency_id
 */
final class SlcWallet
{
    private int $currency_id;
    private int $user_id;

    public function __construct(int $user_id, int $currency_id) {
//        parent::__construct();
        $this->user_id = $user_id;
        $this->currency_id = $currency_id;
    }
    public static function getForUser(int $user_id, int $currency): SlcWallet
    {
        return new SlcWallet($user_id, $currency);
//        $wallet = self::where('user_id', $user_id)
//            ->where('currency_id', $currency)
//            ->lockForUpdate()
//            ->first();
//
//        if ($wallet === null) {
//            $wallet = new SlcWallet();
//            $wallet->user_id = $user_id;
//            $wallet->amount = 100;
//            $wallet->currency_id = $currency;
//            $wallet->save();
//        }
//        return $wallet;
    }
//
//    public function getId(): int
//    {
//        return $this->id;
//    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getBalance(): int
    {
        return KSlc::getInstance()->getSlcBalance($this->user_id, $this->currency_id);
    }

    public function add(int $amount, string $comment = ""): self
    {

        KSlc::getInstance()->addSlcBalance($this->user_id, $this->currency_id, $amount, $comment);
//        SlcLog::log($this->id, $amount, $comment);
        return $this;
//        DB::beginTransaction();
//        $this->increment('amount', $amount);
//        DB::commit();
//
//
//        return $this;
    }

    public function rem(int $amount, string $comment = ""): self
    {
        $balance = KSlc::getInstance()->remSlcBalance($this->user_id, $this->currency_id, $amount, $comment);
        if ($balance === null) throw new \Exception("Not enough Slc");
//        SlcLog::log($this->id, -$amount, $comment);
        return $this;
//        DB::beginTransaction();
//        /** @var SlcWallet $value */
//        $value = self::where('id', $this->id)->lockForUpdate()->first();
//        if ($value->amount < $amount) throw new NotEnoughMoneyException("Not enough Slc");
//        $this->decrement('amount', $amount);
//        DB::commit();
//
//        SlcLog::log($this->id, -$amount, $comment);
//
//        return $this;
    }

//    public function setAmount(int $amount, string $comment = ""): self
//    {
//        DB::beginTransaction();
//        /** @var SlcWallet $wallet */
//        $wallet = self::where('id', $this->id)->lockForUpdate()->first();
//        $wallet->amount = $amount;
//        $wallet->save();
//        DB::commit();
//
//        SlcLog::log($this->id, $amount, $comment);
//
//        return $this;
//    }

    public function save(){}
}
