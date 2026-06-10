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
        Schema::table('user_crypto_wallets', function (Blueprint $table) {
            $table->string('fiat_currency')->after('currency')->nullable();
            $table->string('service_user_id')->after('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_crypto_wallets', function (Blueprint $table) {
            $table->dropColumn(['fiat_currency', 'service_user_id']);
        });
    }
};
