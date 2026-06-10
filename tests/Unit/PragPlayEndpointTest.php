<?php

namespace Tests\Unit;

use App\Gameproviders\PragmaticPlay\Models\PragWallet;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PragPlayEndpointTest extends TestCase
{
    protected $key = "p2uS8DP2d8y4s7ZA";

    public function testEndpoint()
    {
        dump("testing PragmaticPlay");

        $user = MUser::find(12);
        $user->currency = UserWallet::CURRENCY_RUB;
        $user->save();

        $wallet = PragWallet::getForUser($user);
        $this->assertNotNull($wallet);
        $wallet->token = Str::random(32);
        $wallet->amount = 100;
        $wallet->save();

        //auth
        dump("testing auth");
        dump("data");
        $data = [
            'token' => $wallet->token,
            'providerId' => 'PragmaticPlay',
            'gameId' => 'vs5aztecgems',
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        dump(action('Endpoints\PragPlayController@auth'));
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@auth'), $data)->getContent(), true);
        $this->assertIsArray($resp);
        $this->assertIsString($resp['userId']);
        $this->assertEquals($resp['userId'], '12RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals(floatval($resp['cash']), floatval('100.00'));
        dump("response");
        dump($resp);
        $userId = $resp['userId'];
        dump("auth done");

        //balance
        dump("testing balance");
        dump("data");
        $data = [
            'token' => $wallet->token,
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@balance'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '100.00');
        dump("balance done");

        //bet
        dump("testing bet");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@bet'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '90.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("bet done");

        //same bet bet
        dump("testing same bet");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@bet'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $transactionId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '90.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        dump("same bet done");


        //refund
        dump("testing refund");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@refund'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '100.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $refundTrId = $resp['transactionId'];
        dump("refund bet done");

        //refund
        dump("testing same refund");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@refund'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $refundTrId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '100.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $refundTrId = $resp['transactionId'];
        dump("same refund done");

        //win
        dump("testing win");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@result'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '110.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("win done");

        //same win
        dump("testing same win");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '10.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@result'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $transactionId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '110.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        dump("same win done");
    }

    public function testBonusFeatures()
    {
        $user = MUser::find(12);
        $user->currency = UserWallet::CURRENCY_RUB;
        $user->save();

        $wallet = PragWallet::getForUser($user);
        $this->assertNotNull($wallet);
        $wallet->token = Str::random(32);
        $wallet->amount = 100;
        $wallet->save();

        //auth
        dump("testing auth");
        dump("data");
        $data = [
            'token' => $wallet->token,
            'providerId' => 'PragmaticPlay',
            'gameId' => 'vs5aztecgems',
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        dump(action('Endpoints\PragPlayController@auth'));
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@auth'), $data)->getContent(), true);
        $this->assertIsArray($resp);
        $this->assertIsString($resp['userId']);
        $this->assertEquals($resp['userId'], '12RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals(floatval($resp['cash']), floatval('100.00'));
        dump("response");
        dump($resp);
        $userId = $resp['userId'];
        dump("auth done");


        //bonus win should not add money if its already added in win
        dump("testing bonus win");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '15.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@bonusWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '100.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("bonus win done");

        //bonus win should not add money if its already added in win
        dump("testing same bonus win");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '15.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@bonusWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $transactionId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '100.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("same bonus win done");

        //jackpot win
        dump("testing jackpot win");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'jackpotId' => '125',
            'amount' => '200.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@jackpotWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '300.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("jackpot win done");

        //same jackpot win
        dump("testing same bonus win");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'jackpotId' => '125',
            'amount' => '200.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@jackpotWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $transactionId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '300.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("same jackpot win done");

        //promo win
        dump("testing promo win");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '15.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@promoWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '315.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("promo win done");

        //same promo win
        dump("testing same promo win");
        dump("data");
//        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '15.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@promoWin'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertEquals($resp['transactionId'], $transactionId);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '315.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("same promo win done");
    }

    private function calcHash(array $data = []):string
    {
        if (isset($data['hash'])) {
            unset($data['hash']);
        }
        ksort($data, SORT_STRING);
        return md5(http_build_query($data).$this->key);
    }

    public function testBets()
    {
        dump("testing PragmaticPlay bets");

        $user = MUser::find(12);
        $user->currency = UserWallet::CURRENCY_RUB;
        $user->save();

        $wallet = PragWallet::getForUser($user);
        $this->assertNotNull($wallet);
        $wallet->token = Str::random(32);
        $wallet->amount = 10000;
        $wallet->save();

        //auth
        dump("testing auth");
        dump("data");
        $data = [
            'token' => $wallet->token,
            'providerId' => 'PragmaticPlay',
            'gameId' => 'vs5aztecgems',
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        dump(action('Endpoints\PragPlayController@auth'));
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@auth'), $data)->getContent(), true);
        $this->assertIsArray($resp);
        $this->assertIsString($resp['userId']);
        $this->assertEquals($resp['userId'], '12RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals(floatval($resp['cash']), floatval('10000.00'));
        dump("response");
        dump($resp);
        $userId = $resp['userId'];
        dump("auth done");

        //balance
        dump("testing balance");
        dump("data");
        $data = [
            'token' => $wallet->token,
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@balance'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '10000.00');
        dump("balance done");

        //bet
        dump("testing bet");
        dump("data");
        $ref = uniqid(time());
        $data = [
            'providerId' => 'PragmaticPlay',
            'userId' => $userId,
            'gameId' => 'vs5aztecgems',
            'roundId' => '1123',
            'amount' => '1000.00',
            'reference' => $ref,
            'timestamp' => time(),
        ];
        $data['hash'] = $this->calcHash($data);

        dump($data);
        $resp = json_decode($this->post(action('Endpoints\PragPlayController@bet'), $data)->getContent(), true);
        dump("response");
        dump($resp);

        $this->assertIsArray($resp);
        $this->assertIsString($resp['transactionId']);
        $this->assertNotEmpty($resp['transactionId']);
        $this->assertIsString($resp['currency']);
        $this->assertEquals($resp['currency'], 'RUB');
        $this->assertIsString($resp['cash']);
        $this->assertEquals($resp['cash'], '9000.00');
        $this->assertIsString($resp['bonus']);
        $this->assertEquals($resp['bonus'], '0.00');
        $transactionId = $resp['transactionId'];
        dump("bet done");
    }
}
