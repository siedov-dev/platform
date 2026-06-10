<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function () {return response("ok");});

Route::group(['middleware' => 'forceJson'], function() {
    Route::post('/piastrix_pay_url', 'PayController@piastrix_pay_url');

//    Route::post('vegasendpoint', 'VegasEndpointController@process');
//    Route::post('dm-endpoint', 'DrummatikApiController@endpoint');
//    Route::any('/slotegcb', 'SlotegratorEndpointController@endpoint');
//    Route::any('/stage/slotegcb', [\App\Http\Controllers\Endpoints\SlegStageEndpointController::class, 'endpoint']);
//    Route::post('/enotcallback', 'PayController@enot_pay_callback');

    Route::any('/settlepay/pay', 'PayController@settlepay');
    Route::any('/settlepay/payout', 'PayController@settlepayout');
//    Route::any('/freekassa/pay', 'PayController@fk_pay');
    Route::any('/pay/freekassa', [\App\PaymentsAPI\FreeKassa\FkApi::class, 'depositCallback']);
//    Route::any('/freekassa/pay', [\App\PaymentsAPI\FreeKassa\FkApi::class, 'depositCallback']);
//    Route::any('/ali/pay', 'PayController@ali_pay');
    Route::post('/brpay/pay', [\App\PaymentsAPI\Brpay\Brpay::class, 'depositCallback']);
//    Route::any('/swiftpay/pay', 'PayController@swift_pay');
//    Route::any('/interkassa/pay', 'PayController@ik_pay');
//    Route::post('/swp2p/pay', 'PayController@swp2p_pay');
//    Route::any('/paze/pay', 'PayController@pazePay');
    Route::any('/paycos/pay', [\App\PaymentsAPI\Paycos\Paycos::class, 'depositCallback']);
//    Route::any('/pay/paygate10', 'PayController@payGatePay');
//    Route::any('/pay/cryptocloud', 'PayController@cryptoCloud');
//    Route::any('/pay/alphapo', 'PayController@alphapoCallback');
    Route::any('/pay/kvitum', [\App\PaymentsAPI\Kvitum\Kvitum::class, 'depositCallback']);
//    Route::any('/pay/paylama', 'PayController@paylama');
//    Route::any('/einpayz/deposit', 'PayController@einpayzDeposit');
//    Route::any('/einpayz/withdraw', 'PayController@einpayzWithdraw');
//    Route::any('/pay/paykassa', 'PayController@payKassa');
    Route::any('/pay/cryptomus', [\App\PaymentsAPI\Cryptomus\Cryptomus::class, 'cryptomusCallback']);
//    Route::any('/pay/alfakit', 'PayController@alfakitPay');
    Route::any('/pay/betatransfer', [\App\PaymentsAPI\Betatransfer\Betatransfer::class, 'depositCallback']);
    Route::any('/pay/sredapay', [\App\PaymentsAPI\SredaPay\SredaPay::class, 'payCallback']);
    Route::any('/pay/sredavisa', [\App\PaymentsAPI\SredaPay\SredaPayVisa::class, 'payCallback']);
    Route::any('/payout/sreda', [\App\PaymentsAPI\SredaPay\SredaPay::class, 'payoutCallback']);
    Route::any('/pay/paycenter', [\App\PaymentsAPI\PaymentCenter\PaymentCenter::class, 'depositCallback']);
    Route::any('/payout/paycenter', [\App\Payout\PayCenterPayout::class, 'payoutCallback']);
    Route::any('/pay/antarex', [\App\PaymentsAPI\Antarex\Antarex::class, 'depositCallback']);
    Route::any('/pay/b2pay', [\App\PaymentsAPI\B2Pay\B2Pay::class, 'depositCallback']);
    Route::any('/pay/postman', [\App\PaymentsAPI\Postman\Postman::class, 'depositCallback']);
    Route::any('/pay/inwizo', [\App\PaymentsAPI\Inwizo\Inwizo::class, 'depositCallback']);
    Route::any('/pay/inwizo/confirm', [\App\PaymentsAPI\Inwizo\Inwizo::class, 'confirmCallback']);
    Route::any('/pay/aeza', [\App\PaymentsAPI\AezaPay\AezaPay::class, 'depositCallback']);
    Route::any('/pay/paydex', [\App\PaymentsAPI\Paydex\Paydex::class, 'depositCallback']);
    Route::any('/pay/sandbox', [\App\PaymentsAPI\Sandbox\Sandbox::class, 'depositCallback']);
    Route::any('/pay/aifory', [\App\PaymentsAPI\Aifory\Aifory::class, 'depositCallback']);
    Route::any('/pay/nashobmen', [\App\PaymentsAPI\NashObmen\NashObmen::class, 'depositCallback']);
    Route::any('/pay/gateexpress', [\App\PaymentsAPI\GateExpress\GateExpress::class, 'depositCallback']);
    Route::any('/pay/empayre', [\App\PaymentsAPI\Empayre\Empayre::class, 'depositCallback']);
    Route::any('/pay/crosspay', [\App\PaymentsAPI\CrossPay\CrossPay::class, 'depositCallback']);
    Route::any('/pay/chatl', [\App\PaymentsAPI\ChatlPay\ChatlPay::class, 'depositCallback']);
    Route::any('/pay/expay', [\App\PaymentsAPI\ExPay\ExPay::class, 'depositCallback']);
    Route::any('/pay/crownpay', [\App\PaymentsAPI\PayCrown\PayCrown::class, 'depositCallback']);
    Route::any('/pay/changelly', [\App\PaymentsAPI\Changelly\Changelly::class, 'depositCallback']);
    Route::any('/pay/passimpay', [\App\PaymentsAPI\Passimpay\Passimpay::class, 'depositCallback']);
    Route::any('/pay/payadmit', [\App\PaymentsAPI\Payadmit\Payadmit::class, 'depositCallback']);
    Route::any('/pay/anypay', [\App\PaymentsAPI\AnyPay\AnyPay::class, 'depositCallback']);
    Route::any('/pay/aifory/deposit', [\App\PaymentsAPI\Aifory\Aifory::class, 'depositCallback']);
    Route::any('/pay/aifory/withdraw', [\App\PaymentsAPI\Aifory\Aifory::class, 'withdrawCallback']);
    Route::any('/pay/billbland', [\App\PaymentsAPI\BillBlend\BillBlend::class, 'depositCallback']);
    Route::any('/pay/bestpay24/deposit', [\App\PaymentsAPI\BestPay24\BestPay24::class, 'depositCallback']);
    Route::any('/pay/bestpay24/withdraw', [\App\PaymentsAPI\BestPay24\BestPay24::class, 'withdrawCallback']);
    Route::any('/pay/paycraft/callback', [\App\PaymentsAPI\PayCraft::class, 'depositCallback'])->name('payin.paycraft.callback');
    Route::any('/pay/mayfpay', [\App\PaymentsAPI\MayfPay\MayfPay::class, 'depositCallback']);
    Route::any('/pay/payonhost/callback', [\App\PaymentsAPI\PayOnHost::class, 'callback']);
    Route::any('/pay/repay/callback', [\App\PaymentsAPI\Repay\Repay::class, 'callback'])->name('pay.repay.callback');
    Route::any('/payout/repay/callback', [\App\PaymentsAPI\Repay\Repay::class, 'callback_payout']);
    Route::get('/pay/streampay/callback', [\App\PaymentsAPI\StreamPay::class, 'callback']);
    Route::any('/pay/turcode/callback', [\App\PaymentsAPI\Turcode::class, 'callback']);
    Route::post('/pay/TreeDS', [\App\Http\Controllers\PayController::class, 'treeDsCallback']);
    Route::any('/pay/xpaypro/webhook', [\App\PaymentsAPI\XPayPro\XPayPro::class, 'webhook']);
    Route::any('/pay/jupiter', [\App\PaymentsAPI\Jupiter\Jupiter::class, 'callback']);
    Route::any('/pay/coocoopay/callback', [\App\PaymentsAPI\CooCooPay\CooCooPay::class, 'depositCallback']);
    Route::any('/pay/luckypay/callback', [\App\PaymentsAPI\LuckyPay\LuckyPay::class, 'callback']);
    Route::any('/pay/payscrow/callback', [\App\PaymentsAPI\Payscrow\Payscrow::class, 'callback']);
    Route::any('/pay/bitzone', [\App\PaymentsAPI\BitZone\BitZone::class, 'depositCallback']);
    Route::any('/pay/payme', [\App\PaymentsAPI\Payme\Payme::class, 'depositCallback']);
    Route::get('/pay/easybits', [\App\PaymentsAPI\EasyBits\EasyBits::class, 'depositCallback']);
    Route::any('/pay/duali/callback', [\App\PaymentsAPI\DualiPay\DualiPay::class, 'depositCallback']);
    Route::any('/pay/duali/payout', [\App\PaymentsAPI\DualiPay\DualiPay::class, 'payoutCallback']);
    Route::any('/pay/nirvana/callback', [\App\PaymentsAPI\NirvanaPay\NirvanaPay::class, 'depositCallback']);
    Route::any('/pay/nirvana/payout', [\App\PaymentsAPI\NirvanaPay\NirvanaPay::class, 'payoutCallback']);
    Route::any('/pay/infinity/callback', [\App\PaymentsAPI\InfinityPay\InfinityPay::class, 'depositCallback']);
    Route::any('/pay/oneplat/callback', [\App\PaymentsAPI\OnePlat\OnePlat::class, 'depositCallback']);
    Route::any('/pay/onevision/callback', [\App\PaymentsAPI\OneVision\OneVision::class, 'depositCallback']);
    Route::any('/pay/hh/deposit', [\App\PaymentsAPI\HighHelp\HighHelp::class, 'depositCallback']);
    Route::post('/pay/shadowpays/callback', [\App\PaymentsAPI\ShadowPays\ShadowPays::class, 'callback']);
    Route::post('/pay/sequoia/deposit', [\App\PaymentsAPI\SequoiaPay\SequoiaPayApi::class, 'depositCallback']);
    Route::post('/pay/sequoia/payout', [\App\PaymentsAPI\SequoiaPay\SequoiaPayApi::class, 'payoutCallback']);
    Route::any('/pay/exotic/deposit', [\App\PaymentsAPI\Exotic\ExoticApi::class, 'depositCallback']);
    Route::any('/pay/exotic/payout', [\App\PaymentsAPI\Exotic\ExoticApi::class, 'payoutCallback']);
    Route::any('/pay/pspy/deposit', [\App\PaymentsAPI\Pspy\PspyApi::class, 'depositCallback']);
    Route::any('/pay/proctech/deposit', [\App\PaymentsAPI\ProcTech\ProcTechApi::class, 'depositCallback']);
    Route::any('/pay/guru', [\App\Http\Controllers\Payments\PayCallbackController::class, 'guruDeposit']);
    Route::any('/pay/feni', [\App\Http\Controllers\Payments\PayCallbackController::class, 'feniDeposit']);
    Route::any('/pay/noros', [\App\Http\Controllers\Payments\PayCallbackController::class, 'norosDeposit']);

    Route::post('/pay/skypay/callback', \App\Http\Controllers\Payments\SkyPayApiCallback::class)->name('pay.skypay.callback');

//    Route::any('/pragplay/{method}', 'Endpoints\PragPlayController@endpoint');
//    Route::any('/pragplay', 'Endpoints\PragPlayController@endpoint');
//    Route::any('/pragplay/authenticate.html', 'Endpoints\PragPlayController@auth');
//    Route::any('/pragplay/balance.html', 'Endpoints\PragPlayController@balance');
//    Route::any('/pragplay/bet.html', 'Endpoints\PragPlayController@bet');
//    Route::any('/pragplay/refund.html', 'Endpoints\PragPlayController@refund');
//    Route::any('/pragplay/result.html', 'Endpoints\PragPlayController@result');
//    Route::any('/pragplay/bonusWin.html', 'Endpoints\PragPlayController@bonusWin');
//    Route::any('/pragplay/jackpotWin.html', 'Endpoints\PragPlayController@jackpotWin');
//    Route::any('/pragplay/promoWin.html', 'Endpoints\PragPlayController@promoWin');
//    Route::any('/pragplay/endRound.html', 'Endpoints\PragPlayController@endRound');
//    Route::any('/pragplay/sessionExpired.html', 'Endpoints\PragPlayController@sessionExpired');

//    Route::any('/pplaystage/', 'Endpoints\PragPlayStageController@endpoint');
//    Route::any('/pplaystage/authenticate.html', 'Endpoints\PragPlayStageController@auth');
//    Route::any('/pplaystage/balance.html', 'Endpoints\PragPlayStageController@balance');
//    Route::any('/pplaystage/bet.html', 'Endpoints\PragPlayStageController@bet');
//    Route::any('/pplaystage/refund.html', 'Endpoints\PragPlayStageController@refund');
//    Route::any('/pplaystage/result.html', 'Endpoints\PragPlayStageController@result');
//    Route::any('/pplaystage/bonusWin.html', 'Endpoints\PragPlayStageController@bonusWin');
//    Route::any('/pplaystage/jackpotWin.html', 'Endpoints\PragPlayStageController@jackpotWin');
//    Route::any('/pplaystage/promoWin.html', 'Endpoints\PragPlayStageController@promoWin');
//    Route::any('/pplaystage/endRound.html', 'Endpoints\PragPlayStageController@endRound');
//    Route::any('/pplaystage/sessionExpired.html', 'Endpoints\PragPlayStageController@sessionExpired');

    Route::any('/balance', 'WalletController@getBalance');

    //bet production
    Route::any('/betproduction/GetPlayerData', [\App\Http\Controllers\BetProductionController::class, 'getPlayerData']);
    Route::any('/betproduction/DoBet', [\App\Http\Controllers\BetProductionController::class, 'doBet']);
    Route::any('/betproduction/sportsbook/player', [\App\Http\Controllers\BetProductionController::class, 'getPlayerData']);
    Route::any('/betproduction/sportsbook/bet', [\App\Http\Controllers\BetProductionController::class, 'doBet']);


    Route::group(['prefix' => 'tv-bet'], function() {
        Route::get('/users/auth', 'TVBetController@usersAuth');
    });


    Route::group(['prefix' => 'bonus-ctl'], function() {
        Route::post('/activate', 'GetBonusController@activateByCrm');
    });

    Route::group(['prefix' => 'user-ctl'], function() {
        Route::post('/deposit', 'PayController@crmDeposit');
    });

    Route::group(['prefix' => 'promo-ctl'], function() {
        Route::post('/activate', 'BonusPromocodesController@crmActivate');
    });

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

//    Route::post('viber', function (Request $request) {
//        return \App\Viber\ViberBot::update($request);
//    });
//    Route::group(['prefix' => 'qt/'], function() {
//        Route::get('/accounts/{playerId}/session', [\App\Http\Controllers\Endpoints\QtRestController::class, 'verifySession']);
//        Route::get('/accounts/{playerId}/balance', [\App\Http\Controllers\Endpoints\QtRestController::class, 'getBalance']);
//        Route::post('/transactions', [\App\Http\Controllers\Endpoints\QtRestController::class, 'transaction']);
//        Route::post('/transactions/rollback', [\App\Http\Controllers\Endpoints\QtRestController::class, 'rollback']);
//        Route::post('/promotion', [\App\Http\Controllers\Endpoints\QtRestController::class, 'promotionStatus']);
//        Route::post('/bonus/rewards', [\App\Http\Controllers\Endpoints\QtRestController::class, 'rewards']);
//    });
});
//Route::any('/softswiss/{method}', [\App\Http\Controllers\Endpoints\SoftSwissController::class, 'endpoint']);

Route::post('/paycraft/{transaction_id}/check', [\App\Http\Controllers\Payments\PayCraft::class, 'checkStatus'])
    ->name('payin.paycraft.check_status')
    ->where('transaction_id', '[0-9]+');

Route::any('/gm/{path?}', [\App\Http\Controllers\GmProxyController::class, 'proxy'])
    ->where('path', '.*');

Route::group(['prefix' => 'bcknd', 'middleware' => 'apiToken'], function () {
   Route::post('depositCompleteEvent', [\App\Http\Controllers\BackEndController::class, 'depositCompleteEvent']);
});
