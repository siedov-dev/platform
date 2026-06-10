<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCryptoFieldToExchangeRateCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_rate_caches', function (Blueprint $table) {
            //
            $table->boolean('is_crypto')->default(false)->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_rate_caches', function (Blueprint $table) {
            //
            $table->dropColumn('is_crypto');
        });
    }
}
