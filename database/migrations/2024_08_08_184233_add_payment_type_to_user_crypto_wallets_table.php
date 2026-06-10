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
            $table->enum('payment_type', [0, 1])->index()->after('user_id');
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
            $table->dropColumn('payment_type');
        });
    }
};
