<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Payin\PayCraftPayin;
use App\PayoutTransaction;

class PayCraft extends Controller
{
    public function checkStatus($a, $transaction_id)
    {
        $transaction = PayoutTransaction::where('id', $transaction_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        return PayCraftPayin::checkStatus($transaction);
    }
}
