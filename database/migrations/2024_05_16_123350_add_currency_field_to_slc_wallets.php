<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slc_wallets', function (Blueprint $table) {
            //
            $table->integer('currency_id')->default(0);
        });

        //set current wallet currency to user's current currency
        foreach (\App\Models\Slc\SlcWallet::cursor() as $wallet) {
            $user = \App\MUser::find($wallet->getUserId());
            $wallet->currency_id = $user?->currency?? \App\UserWallet::CURRENCY_RUB;
            $wallet->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_wallets', function (Blueprint $table) {
            //
            $table->dropColumn('currency_id');
        });
    }
};
