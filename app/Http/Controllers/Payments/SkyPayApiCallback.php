<?php

namespace App\Http\Controllers\Payments;

use App\Helpers\Hlog;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentCallback\SkyPayRequest;
use App\Models\PaymentSystems;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\SkyPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SkyPayApiCallback extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function __invoke(SkyPayRequest $request)
    {

        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        //Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        //Log::channel('pay')->info(var_export($request->all(), true));

        try {
            DB::beginTransaction();
            $transaction = PayoutTransaction::where('o_tr_id', $request->get('payment_id'))
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_SKYPAY_PENDING,
                    PayoutTransaction::STATUS_SKYPAY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if (!SkyPay::callbackCheckSignature()) {
                return response([
                    'status' => SkyPay::callbackCheckSignature(),
                ], 500);
            }

            if ($transaction === null) {
                return response([
                    'status' => SkyPay::callbackCheckSignature(),
                ], 404);
            }

            if ($transaction?->status_id === PayoutTransaction::STATUS_SKYPAY_FAIL) Log::channel('pay')->info(__CLASS__." transaction was in fail state");

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($request->get('status') == 2) {
                $currency_id = str_replace(
                    ['rub', 'uah', 'usd', 'kzt'],
                    [UserWallet::CURRENCY_RUB, UserWallet::CURRENCY_UAH, UserWallet::CURRENCY_USD, UserWallet::CURRENCY_KZT],
                    $request->currency
                );

                Payin::completeTransaction($user, $transaction, $request->get('amount'), PayoutTransaction::STATUS_SKYPAY_SUCCESS, $currency_id);
            }

            return response([
                'status' => SkyPay::callbackCheckSignature(),
            ], 200);
        } catch (\Throwable $e) {
            Hlog::logException($e);
            DB::rollBack();
        } finally {
            DB::commit();
        }

        return response([
            'status' => SkyPay::callbackCheckSignature(),
        ], 500);
    }
}
