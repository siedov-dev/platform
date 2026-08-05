<?php

use App\Helpers\Localization;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//Route::get('/pay/obmenka/deposit', 'PayController@obmenka_deposit');
//Route::get('/pay/obmenka/payout', 'PayController@obmenka_payout');

//Route::post('/pay/billLine/deposit', 'PayController@billLine_deposit');
//Route::post('/pay/billLine/process', 'PayController@billLine_process');
//Route::post('/pay/billLine/success', 'PayController@billLine_success');
//Route::post('/pay/billLine/success', 'PayController@billLine_success');
//Route::post('/pay/billLine/fail', 'PayController@billLine_fail');

//Route::post('/pay/enot', 'PayController@enot_pay');
//Route::post('/perfect-success', 'PayController@perfect_money_success');
//Route::post('/perfect-error', 'PayController@perfect_money_error');

Route::any('/', 'IndexController@index')->name('main.index');
Route::any('/pay/success', 'PayController@success');
Route::any('/pay/fail', 'PayController@fail');

Route::get('/tv-bet/play', 'TVBetController@play');
Route::get('/tv-bet/frame', 'TVBetController@frame')->name('tvBet.frame');
Route::post('/tv-bet/users/auth', 'TVBetController@usersAuth');

// live api endpoints
Route::post('/GetUserData', 'Live\IndexController@get_user_data');
Route::post('/MakePayment', 'Live\IndexController@payment');
Route::post('/GetPaymentInfo', 'Live\IndexController@payment_info');
Route::post('/GetPromoInfo', 'Live\IndexController@promo_info');
Route::post('/RefreshToken', 'Live\IndexController@refresh_token');

//Route::post('/mail-verification',     'AuthController@mailVerification');

Route::post('/setNotificationStatus', 'NotificationsController@setNotificationStatus');
Route::post('/otp-confirm',  'PayController@confirmOtpCode')->name('otp-confirm');

//Route::get('/ad/join-with-bonus', 'AuthController@joinWithBonusByToken'); //deprecated?
Route::post('/join-with-bonus', 'AuthController@joinWithBonusByParams');
Route::post('/reg-with-bonus', 'AuthController@regWithBonusByParams');
Route::post('/reg/preland', 'AuthController@regPreland');

Route::get('/captcha', [\App\Http\Controllers\SimpleCaptchaController::class, 'generate'])->name('captcha');

Route::group(['prefix' => '{locale}'], function() {

    // Auth
    Route::get('/login', 'IndexController@index')->name('login');
    Route::post('/login', 'AuthController@login');

    Route::get('/login-form', 'IndexController@loginForm')->name('login.form');
    Route::get('/register-form', 'IndexController@registerForm')->name('register.form');

    Route::get('/register', 'IndexController@index')->name('register');
    Route::get('/register/phone', 'IndexController@index')->name('register.phone');
    Route::post('/register', 'AuthController@register');

    Route::get('/auto-login',               'AuthController@loginByUrl');
    Route::get('/logout',                   'AuthController@logout')->name('logout');

    Route::post('/change-login',                   'CabinetController@changeLogin')->name('changeLogin');

    Route::post('/forgot-pass',              'AuthController@forgotPassword')->name('forgotPass');

    Route::post('change-pass',              'AuthController@changePass');
    Route::post('/change-acc-data',              'AuthController@changeAccountData');
    Route::post('/social-login',            'AuthController@socialLogin')->name('socialLogin');

//    Route::post('confirmation-pass',        'AuthController@confirmation_pass');
//    Route::get('/verify-email',             'AuthController@verifyEmail');
//    Route::post('/user-verification',       'AuthController@userVerification');

    Route::get('/subscribe/{token}', 'SubscribeController@subscribe')->name('subscribe');
    Route::get('/unsubscribe/{token}', 'SubscribeController@unsubscribe')->name('unsubscribe');

    Route::post('/subscribe', 'SubscribeController@subscribeCheck')->name('subscribe.check');

//    Route::get('/unsubscribe',              'AuthController@unsubscribe')->name('unsubscribe');

    //Route::get('/join-with-bonus', 'AuthController@joinWithBonusByParams');


    Route::get('/games-by-tag', 'GamesController@gamesByTag')->name('gamesByTag');

    Route::get('/games/search', 'GamesController@searchGames')->name('games.search');

    Route::group(['middleware' => 'returnMoney'], function() {
        Route::get('/games', 'GamesController@index')->name('games');
        Route::get('/games/slots', 'GamesController@index')->name('games.slots');
        Route::get('/games/live', 'GamesController@index')->name('games.live');
        Route::get('/games/table', 'GamesController@index')->name('games.table');
        Route::get('/games/sport', 'GamesController@index')->name('games.sport');

        Route::get('/games/crash', 'GamesController@index')->name('games.crash');
        Route::get('/games/new', 'GamesController@index')->name('games.new');
        Route::get('/games/top', 'GamesController@index')->name('games.top');
        Route::get('/games/hot', 'GamesController@index')->name('games.hot');
        Route::get('/games/megaways', 'GamesController@index')->name('games.megaways');
        Route::get('/games/fs', 'GamesController@index')->name('games.fs');
        Route::get('/games/jackpot', 'GamesController@index')->name('games.jackpot');

        Route::get('/games/bonus', 'GamesController@index')->name('games.bonus');
        Route::get('/games/tournament', 'GamesController@index')->name('games.tournament');

        Route::get('/games/tv-bet', 'Live\IndexController@index')->name('games.tvBet');
        Route::get('/games/tv-bet/frame', 'Live\IndexController@frame')->name('games.tv.frame');
        Route::get('/games/favourites', 'GamesController@favourites')->name('games.favourites');
        Route::get('/games/bet-production', 'BetProductionController@play')->name('games.BetProduction');

        Route::get('/referral-system', 'IndexController@referralSystem')->name('referralSystem');

        Route::get('/hall-of-fame', 'HallOfFameController@index')->name('hallOfFame');
        Route::get('/bonuses', 'BonusesController@index')->name('bonuses');

        Route::get('tournaments',       'TournamentController@index')->name('tournaments');
        Route::get('tournaments/{tournament}', 'TournamentController@info')->name('tournaments.info');
        Route::post('tournament/participate', 'TournamentController@participate')->name('tournaments.participate');
        Route::get('tournaments/promo/{tournament}', 'TournamentController@promoInfo')->name('tournaments.promo-info');

    });
    Route::post('tournament/table', 'TournamentController@gameTableData')->name('tournaments.table');

    Route::get('/bonuses/{id}', 'BonusesController@info')->name('bonuses.info');
    Route::get('/bonus-count', 'BonusesController@bonusCounter')->name('bonus.count');
    Route::get('/bonus-lane', 'BonusesController@bonusLane')->name('bonus.lane');
    Route::get('/fdep-info', 'BonusesController@firstDepInfo')->name('bonus.fdep-info');


    Route::any('/',               	'IndexController@index')->name('landing');
    Route::any('/welcome',               	'IndexController@welcome')->name('welcome');
    Route::get('/deposit',               	'IndexController@showDeposit')->name('deposit');
    Route::any('/promocode',               	'IndexController@showHavePromocode');


    Route::get('/verify-email', 'ContactsVerificationController@verifyEmail')->name('cabinet.verifyEmail');
    // Cabinet
    Route::group(['middleware' => 'auth'], function() {
        Route::get('/cabinet', 'CabinetController@account')->name('cabinet');
        Route::get('/cabinet/account', 'CabinetController@account')->name('cabinet.account');
        Route::get('/cabinet/transactions', 'CabinetController@transactions')->name('cabinet.transactions');
        Route::post('/cabinet/transactions/cancel', 'CabinetController@cancelPayout')->name('cabinet.transactions.cancel');
        Route::get('/cabinet/bonuses', 'CabinetController@bonuses')->name('cabinet.bonuses');

        Route::get('/cabinet/sport', 'CabinetController@sportHistory')->name('cabinet.sport');

        Route::get('/cabinet/tournaments', 'CabinetController@tournaments')->name('cabinet.tournaments');
        Route::get('/cabinet/settings', 'CabinetController@settings')->name('cabinet.settings');
        Route::get('/cabinet/ranks', 'CabinetController@ranks')->name('cabinet.ranks');
        Route::match(['get', 'post'], '/cabinet/lock', 'CabinetController@selfLock')->name('cabinet.lock');

        Route::get('/verify-phone', 'ContactsVerificationController@verifyPhone')->name('cabinet.verifyPhone');

        Route::post('/send-email-verification', 'ContactsVerificationController@sendVerificationEmail')->name('cabinet.sendEmailVerification');
        Route::post('/send-phone-verification', 'ContactsVerificationController@sendVerificationSMS')->name('cabinet.sendPhoneVerification');
        Route::post('/store-personal-data', 'ContactsVerificationController@setPersonalData')->name('cabinet.personalData');

        Route::get('/wallet', 'WalletController@wallet')->name('wallet');
        Route::get('/wallet/deposit', 'WalletController@deposit')->name('deposit.modal');
        Route::get('/wallet/payout', 'WalletController@payout')->name('payout.modal');
        Route::get('/wallet/buyCrypto', 'WalletController@buyCrypto')->name('deposit.buyCrypto');
        Route::post('/wallet/payout-reqs', 'WalletController@getPayoutRequisites');
        Route::post('/wallet/payin-reqs', 'WalletController@getPayinRequisites');
        Route::post('/wallet/switch', 'WalletController@switchWallet')->name('wallet.switch');

        Route::post('/cascade/requisites', 'PayController@cascadeRequisites')->name('cascade.requisites');
        Route::post('/cascade/deposit', 'PayController@cascadeDeposit')->name('cascade.deposit');

        Route::post('/deposit/confirm', 'PayController@depositConfirm')->name('deposit.confirm');
        Route::post('/deposit/cancel', 'PayController@depositCancel')->name('deposit.cancel');

        Route::post('/payout',  'PayController@payout')->name('payout');
        Route::post('/otpConfirm',  'PayController@confirmOtp')->name('otp.confirm');
        Route::post('/otpResend',  'PayController@resendOtp')->name('otp.resend');

        Route::match(['get', 'post'], '/wallet/add', 'WalletController@add')->name('wallet.add');
        Route::match(['get', 'post'], '/wallet/del/{currency}', 'WalletController@del')->name('wallet.del');

        Route::get('/balance-mode', 'GamesController@balanceModeModal')->name('balanceMode');



        Route::get('/cabinet/partnership', 'ReferralController@cabinet')->name('cabinet.partnership');
        Route::get('/cabinet/eventsPage', 'ReferralController@eventsPage')->name('cabinet.partnership.eventsPage');
        Route::get('/cabinet/refUsersPage', 'ReferralController@refUsersPage')->name('cabinet.partnership.refUsersPage');
        Route::post('/cabinet/partnership/transfer-money', 'ReferralController@transferMoney');
//        Route::post('/cabinet/partnership/transfer-bonus', 'ReferralController@transferBonusMoney');


        Route::post('/cabinet/change-password', 'CabinetController@changePassword')->name('cabinet.changePassword');


//        Route::post('/games/make-favourite', 'GamesController@makeFavourite')->name('games.makeFavourite');

//        Route::any('qr-deposit', 'PayController@showCryptoPayForm');
        Route::post('/nashobmen/check', [\App\PaymentsAPI\NashObmen\NashObmen::class, 'checkPayment']);

        Route::post('/wait-payment', [\App\Http\Controllers\PayController::class, 'waitPayment']);

        Route::group(['prefix' => '/slc'], function (){
            Route::get('/tasks', [\App\Http\Controllers\SlcController::class, 'tasks'])->name('slc.tasks');
            Route::match(['GET', 'POST'], '/taskRewardModal', [\App\Http\Controllers\SlcController::class, 'taskRewardModal'])->name('slc.reward-modal');
            Route::get('/shop', [\App\Http\Controllers\SlcController::class, 'shop'])->name('slc.shop');
            Route::get('/exchange', [\App\Http\Controllers\SlcController::class, 'shop'])->name('slc.exchange');
            Route::match(['GET', 'POST'], '/exchange-modal', [\App\Http\Controllers\SlcController::class, 'exchange'])->name('slc.exchange.modal');
            Route::get('/buy-item', [\App\Http\Controllers\SlcController::class, 'buyItemModal'])->name('slc.buy-item-modal');
            Route::post('/buy', [\App\Http\Controllers\SlcController::class, 'buyItem'])->name('slc.buy-item');
            Route::get('/items', [\App\Http\Controllers\SlcController::class, 'userItems'])->name('slc.user-items');
            Route::get('/itemInfo', [\App\Http\Controllers\SlcController::class, 'itemInfo'])->name('slc.item-info');
            Route::get('/userItemInfo', [\App\Http\Controllers\SlcController::class, 'userItemInfo'])->name('slc.user-item-info');
            Route::any( '/activate-item', [\App\Http\Controllers\SlcController::class, 'activateItem'])->name('slc.activate-item');
            Route::any( '/activate-modal', [\App\Http\Controllers\SlcController::class, 'activateModal'])->name('slc.activate-modal');
            Route::get('/achievements', [\App\Http\Controllers\SlcController::class, 'achievements'])->name('slc.achievements');
            Route::get('/ranks', [\App\Http\Controllers\SlcController::class, 'achievements'])->name('slc.ranks');
            Route::get('/achievements/info', [\App\Http\Controllers\SlcController::class, 'achievementPopup'])->name('slc.achievement-popup');
            Route::get('/achievements/reward', [\App\Http\Controllers\SlcController::class, 'achievementReward'])->name('slc.achievement-reward');
            Route::post('/rank-reward', [\App\Http\Controllers\RanksController::class, 'getReward'])->name('ranks.reward');
        });

        Route::group(['prefix' => '/menu'], function (){
            Route::post('/update', [\App\Http\Controllers\MenuController::class, 'updateMenu'])->name('menu.update');
            Route::post('/updateGames', [\App\Http\Controllers\MenuController::class, 'updateGamesMenu'])->name('menu.updateGames')->middleware('CheckBlock');
        });

        Route::group(['prefix' => '/wheel'], function (){
            Route::get('/', [\App\Http\Controllers\WheelController::class, 'wheelModal'])->name('wheel.modal');
            Route::post('/spin', [\App\Http\Controllers\WheelController::class, 'spin'])->name('wheel.spin');
            Route::post('/takebonus', [\App\Http\Controllers\WheelController::class, 'takeBonus'])->name('wheel.take-bonus');
        });

        Route::group(['prefix' => '/slotmachine'], function (){
            Route::get('/', [\App\Http\Controllers\SlcSlotMachineController::class, 'smModal'])->name('slotmachine.modal');
            Route::post('/spin', [\App\Http\Controllers\SlcSlotMachineController::class, 'spin'])->name('slotmachine.spin');
        });

        Route::group(['prefix' => '/treasures'], function (){
            Route::get('/', [\App\Http\Controllers\SlcController::class, 'treasures'])->name('slc.treasures');
            Route::get('/open', [\App\Http\Controllers\SlcController::class, 'openChest'])->name('slc.open-chest');
            Route::get('/tab', [\App\Http\Controllers\SlcController::class, 'treasureTab'])->name('slc.chest-tab');
        });

        Route::group(['prefix' => '/cashback'], function (){
            Route::get('/', [\App\Http\Controllers\SlcCashbackController::class, 'shipModal'])->name('slc.cashback');
            Route::post('/shoot', [\App\Http\Controllers\SlcCashbackController::class, 'shoot'])->name('slc.cashback.shoot');
        });

        Route::group(['prefix' => '/bonus'], function (){
            Route::get('/multiday-delay', [\App\Http\Controllers\BonusesController::class, 'multiDepDelayDialog'])->name('bonus.multiday-delay');
            Route::get('/popup-info', [\App\Http\Controllers\BonusesController::class, 'popupInfo'])->name('bonus.popup-info');
            Route::post('/drop', [\App\Http\Controllers\GetBonusController::class, 'dropBonus'])->name('bonus.drop');
        });

    });

    Route::group(['prefix' => '/callRequest'], function (){
        Route::get('/', [\App\Http\Controllers\UserCallRequestsController::class, 'renderCallRequestBlock'])->name('callRequest.render');
        Route::post('/makeRequest', [\App\Http\Controllers\UserCallRequestsController::class, 'requestCall'])->name('callRequest.make');
    });

    Route::get('/page-info', [\App\Http\Controllers\SlcController::class, 'pageInfo'])->name('slc.page-info');


    Route::group(['prefix' => '/promos'], function (){
        Route::get('/', [\App\Http\Controllers\PromoController::class, 'promos'])->name('promos');
        Route::get('/promo/{promo_id}', [\App\Http\Controllers\PromoController::class, 'descriptionPage'])->name('promos.description');
    });

    Route::get('/demo-forbidden', 'GamesController@demoForbiddenModal')->name('demoForbidden');

    Route::get('/wallet/balance', 'WalletController@getBalance')->name('wallet.balance');

    Route::get('/status',                  'AuthController@status');
    Route::post('/edit-client',             'AuthController@editClient');

    //User-Confirmation
//    Route::post('/UserConfirmation',            'AuthController@UserConfirmation');
//    Route::get('/UserConfirmation',            'AuthController@UserConfirmation');

    // Password recovery.
    Route::get( '/set-new-password', 'IndexController@index')->name('setNewPassword');
    Route::post('/set-new-password', 'AuthController@setNewPassword');
    Route::post('/check-token', 'AuthController@checkToken')->name('checkPasswordToken');
    Route::get('/confirm-password', 'AuthController@confirmPasswordChange');
    Route::post('/set-new-login', 'AuthController@changeLogin');
//    Route::post('/render-bonus',   'BonusFirstPayController@renderFromBonus');
    Route::match(['get', 'post'], '/get-free-bonus',  'GetBonusController@getFreeBonus');
    Route::get('/get-bonus/{id}', 'GetBonusController@getBonusByScenario');

    Route::post('/transfer-bonus-money/{id}',                'GetBonusController@transferBonusMoney');
    Route::post('/bonus-promo', 'BonusPromocodesController@activate');

//    Route::post('/redirect_privat',                     'Privat_bankController@redirect_privat');
//    Route::match(array('GET', 'POST'),'/get_visa',      'Privat_bankController@GetVisa');
//    Route::post('/visa_pay',                            'Privat_bankController@VisaPay');
//    Route::post('/redirect-free-kassa',                 'Privat_bankController@redirectFreeKassa');
//    Route::get('/success-free-kassa',                   'Privat_bankController@SuccessFreeKassa');
//    Route::get('/error-free-kassa',                     'Privat_bankController@ErrorFreeKassa');

//    Route::get('/cancelApp/{id}',  'PayController@cancelApplication');

//    Route::get('/ranks',                     'IndexController@ranks')->name('ranks');
    Route::get('/footer',                     'FooterController@footerPage')->name('footer.page');

    Route::get('play/{system}/{provider}/{gameId}/{type}', 'EmulatorController@index')->name('games.play');
    Route::post('sesion_status', 'EmulatorController@sesion_status');
    Route::get('redirect', 'EmulatorController@redirect');
//    Route::get('show-bonus', 'IndexController@showBonus');
    Route::get('hall-of-glory', 'IndexController@hallOfGlory');


    Route::post('deposit', 'PayController@deposit');
    Route::any('/pay/close-tab', 'PayController@closeTab')->name('pay.close-tab');
    Route::post('/pay/status', 'PayController@status')->name('pay.status');
    Route::any('/pay/success', 'PayController@success')->name('pay.success');
    Route::any('/pay/success-notify', 'PayController@successNotify')->name('pay.success.notify');
    Route::any('/pay/fail', 'PayController@fail')->name('pay.fail');
    Route::any('/pay/fail-notify', 'PayController@failNotify')->name('pay.fail.notify');

    //wallets

    //bonus activation
    Route::post('/activate-bonus/{id}/{option?}', 'GetBonusController@activateBonus')->name('bonus.activate');
    Route::post('/resume-bonus/{id}/', 'GetBonusController@resumeBonus')->name('bonus.resume');
    Route::post('/pause-bonus/{id}/', 'GetBonusController@pauseBonus')->name('bonus.pause');
    Route::post('/pay-bonus', 'GetBonusController@bonusPay')->name('bonus.pay');
    Route::post('/CancelBonus/{id}', 'GetBonusController@cancelBonus')->name('bonus.cancel');

    Route::get('/wb/deposit', 'GetBonusController@welcomeBonusDeposit')->name('wb.deposit');
    Route::get('/wb/nondep', 'GetBonusController@welcomeBonusNonDep')->name('wb.nondep');
    Route::get('/wb/cond', 'GetBonusController@wbConditions')->name('wb.cond');

    Route::get('/live-casino', 'Live\IndexController@index');
    Route::get('/tvbet', 'Live\IndexController@index');

    Route::post('/updateBonusPad', 'BonusStatusController@updateBonusPad')->name('bonusPad');
    Route::get('/how-money','AuthController@How_money');
    Route::get('/check-money','IndexController@checkMoney');
//    Route::post('/perfect_money_pay', 'PayController@perfect_money_pay');

//    Route::get('/GetLastWin', 'IndexController@getLastWin')->name('lastWin');
//    Route::get('/sitemap.xml', 'SitemapController@index');

    Route::get('/getNotifications', 'NotificationsController@getNotifications');
    Route::get('/getNewNotifications', 'NotificationsController@getNewNotifications');
    Route::get('/getCongrats', 'NotificationsController@getCongratsWindow');

    Route::get('/bonuses-guest', 'BonusesGuestController@index')->name('bonuses_guest');
    Route::get('/bonuses-guest/{bonus}', 'BonusesGuestController@show')->name('bonuses_guest.show');

    Route::get('/closeGame', 'EmulatorController@closeGameFrame');

    Route::any('/sredapay/otp', [\App\PaymentsAPI\SredaPay\SredaPay::class, 'otpConfirm'])->name('sredapay.otp');

    Route::get('/join/{hash}', 'AuthController@joinHashClient');

    Route::group(['prefix' => '/popups'], function (){
        Route::get('/getNew', [\App\Http\Controllers\PopupsController::class, 'getNew']);
        Route::get('/regPopup', [\App\Http\Controllers\PopupsController::class, 'regPopup']);
        Route::post('/unsub', [\App\Http\Controllers\PopupsController::class, 'unsubCategory']);
        Route::post('/timeout', [\App\Http\Controllers\PopupsController::class, 'setTimeout']);
        Route::post('/prolong', [\App\Http\Controllers\PopupsController::class, 'prolongTimeout']);
        Route::get('/authPopup', [\App\Http\Controllers\PopupsController::class, 'authPopup'])->name('popups.auth');
    });

    Route::any('/pay/redirect', [\App\Http\Controllers\PayController::class, 'redirectByTransaction']);
});

Route::post('/authWs/token', [\App\Http\Controllers\SocketController::class, 'wsConnect'])->name('ws.token');

Route::get('/lunopark', function () {
    return view('phone.slc.lunopark.game');
});
