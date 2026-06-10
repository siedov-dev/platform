<?php

namespace Tests\Unit;

use App\GamesProviders\SoftSwiss\Models\SoftSwissWallet;
use App\GamesProviders\SoftSwiss\SoftSwissProviderApi;
use App\Http\Controllers\Endpoints\SoftSwissController;
use App\MUser;
use App\UserWallet;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SoftSwissTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_wallet()
    {
        $this->assertTrue(true);
        dump("testing ".__CLASS__." ". __FUNCTION__);

        $user = MUser::find(12);
        $this->assertTrue($user !== null);
        $user->currency = UserWallet::CURRENCY_RUB;
        $user->save();

        $wallet = SoftSwissWallet::getForUser($user);
        $this->assertTrue($wallet !== null);

        $wallet->amount = 0;
        $wallet->save();

        $this->assertEquals($wallet->getBalanceCents(), 0);
        $this->assertEquals($wallet->getBalance(), 0);

        $wallet->addCents(100);
        $this->assertEquals($wallet->getBalanceCents(), 100);
        $this->assertEquals($wallet->getBalance(), 1);

        $wallet->addCents(1000);
        $this->assertEquals($wallet->getBalanceCents(), 1100);
        $this->assertEquals($wallet->getBalance(), 11);

        $wallet->addCents(1);
        $this->assertEquals($wallet->getBalanceCents(), 1101);
        $this->assertEquals($wallet->getBalance(), 11.01);

        $wallet->addCents(10);
        $this->assertEquals($wallet->getBalanceCents(), 1111);
        $this->assertEquals($wallet->getBalance(), 11.11);

        $wallet->addCents(100000);
        $this->assertEquals($wallet->getBalanceCents(), 101111);
        $this->assertEquals($wallet->getBalance(), 1011.11);

        $wallet->addCents(10000000);
        $this->assertEquals($wallet->getBalanceCents(), 10101111);
        $this->assertEquals($wallet->getBalance(), 101011.11);

        $wallet->remCents(10001101);
        $this->assertEquals($wallet->getBalanceCents(), 100010);
        $this->assertEquals($wallet->getBalance(), 1000.10);

        $wallet->remCents(99909);
        $this->assertEquals($wallet->getBalanceCents(), 101);
        $this->assertEquals($wallet->getBalance(), 1.01);

        $wallet->remCents(11);
        $this->assertEquals($wallet->getBalanceCents(), 90);
        $this->assertEquals($wallet->getBalance(), 0.90);

        $wallet->remCents(91);
        $this->assertEquals($wallet->getBalanceCents(), 0);
        $this->assertEquals($wallet->getBalance(), 0);

        $wallet->addMoney(99.99);
        $this->assertEquals($wallet->getBalanceCents(), 9999);
        $this->assertEquals($wallet->getBalance(), 99.99);

        $wallet->remMoney(9.09);
        $this->assertEquals($wallet->getBalanceCents(), 9090);
        $this->assertEquals($wallet->getBalance(), 90.90);

        $wallet->remMoney(90.90);
        $this->assertEquals($wallet->getBalanceCents(), 0);
        $this->assertEquals($wallet->getBalance(), 0);

        dump("done");
    }

    public function test_endpoint()
    {
        $this->assertTrue(true);
        dump("testing ".__CLASS__." ". __FUNCTION__);

        $api = new SoftSwissProviderApi();

        $user = MUser::find(12);
        $this->assertTrue($user !== null);
        $user->currency = UserWallet::CURRENCY_RUB;
        $user->save();

        $wallet = SoftSwissWallet::getForUser($user);
        $this->assertTrue($wallet !== null);

        $wallet->amount = 0;
        $wallet->save();

        $this->assertEquals($wallet->getBalanceCents(), 0);
        $this->assertEquals($wallet->getBalance(), 0);

        $wallet->addCents(100000);
        $this->assertEquals($wallet->getBalanceCents(), 100000);
        $this->assertEquals($wallet->getBalance(), 1000.00);

        //forbidden
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method', 'play']), []);
        $response->assertStatus(403);
        $response->assertJson(['code' => 403,]);

        //user not found
        $player_id = '13RUB';
        $currency = 'RUB';
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
        ];

        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(500);
        $response->assertJson(['code' => 500, 'message' => 'user not found']);

        //balance
        $player_id = '12RUB';
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 100000]);

        //bet
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 100,
                    'action_id' => uniqid(time()),
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 99900]);

        //number bets
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 100,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'bet',
                    'amount' => 1000,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'bet',
                    'amount' => 50,
                    'action_id' => uniqid(time()),
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 98750]);

        //win
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 100,
                    'action_id' => uniqid(time()),
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 98850]);

        //number wins
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 100,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'win',
                    'amount' => 50,
                    'action_id' => uniqid(time()),
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 100000]);

        //bet and wins
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 50,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'bet',
                    'amount' => 100,
                    'action_id' => uniqid(time()),
                ],
                [
                    'action' => 'win',
                    'amount' => 2000,
                    'action_id' => uniqid(time()),
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 102850]);

        //same id
        $tr_id = uniqid(time());
        //bet
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 2850,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 100000]);
        //same bet
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 2850,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 100000]);

        $tr_id = uniqid(time());
        //win
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);
        //same win
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);

        //rollback
        $tr_id = uniqid(time());
        //bet
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 100000]);
        //rollback
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'rollback',
                    'action_id' => uniqid(time()),
                    'original_action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'rollback']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);

        //win rollback
        $tr_id = uniqid(time());
        //win
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 102000]);
        //rollback
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'rollback',
                    'action_id' => uniqid(time()),
                    'original_action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'rollback']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);

        //pre-rollback
        $tr_id = uniqid(time());
        //rollback
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'rollback',
                    'action_id' => uniqid(time()),
                    'original_action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'rollback']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);
        //bet
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'bet',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);

        $tr_id = uniqid(time());
        //rollback
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'rollback',
                    'action_id' => uniqid(time()),
                    'original_action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'rollback']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);
        //win
        $data = [
            'user_id' => $player_id,
            'currency' => $currency,
            'game' => 'unittest',
            'game_id' => 'unittest',
            'actions' => [
                [
                    'action' => 'win',
                    'amount' => 1000,
                    'action_id' => $tr_id,
                ],
            ],
        ];
        $response = $this->postJson(action('Endpoints\SoftSwissController@endpoint', ['method' => 'play']), $data, ['X-REQUEST-SIGN' => $api->sign(json_encode($data))]);
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson(['balance' => 101000]);
    }
}
