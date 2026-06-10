<?php

namespace Tests\Models\Slc;

use App\Models\Slc\NotEnoughMoneyException;
use App\Models\Slc\SlcWallet;
use Tests\TestCase;

class SlcWalletTest extends TestCase
{
    public function testWallet()
    {
        SlcWallet::where('user_id', 12)->delete();
        $wallet = SlcWallet::getForUser(12);
        self::assertNotNull($wallet);
        $id = $wallet->getId();
        $wallet = SlcWallet::getForUser(12);
        self::assertEquals($wallet->getId(), $id);
    }

    public function testAddRem()
    {
        $wallet = SlcWallet::getForUser(12);
        self::assertNotNull($wallet);
        $balance = $wallet->getBalance();
        $wallet->add(50);
        self::assertEquals($wallet->getBalance(), $balance + 50);
        $wallet->rem(50);
        self::assertEquals($wallet->getBalance(), $balance);
        $wallet->add(10, "test");
        self::assertEquals($wallet->getBalance(), $balance + 10);
        $wallet->rem(10, "test");
        self::assertEquals($wallet->getBalance(), $balance);
    }

    public function testNoptEnoughMoney()
    {
        $this->expectException(NotEnoughMoneyException::class);
        $wallet = SlcWallet::getForUser(12);
        self::assertNotNull($wallet);
        $balance = $wallet->getBalance();
        $wallet->rem($balance + 1, "test");
    }

}
