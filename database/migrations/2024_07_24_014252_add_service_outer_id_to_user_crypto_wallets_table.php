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
            $table->string('service_outer_id')->after('address')->nullable();
            $table->index(['system_id', 'currency', 'service_outer_id']);
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
            $table->dropIndex(['system_id', 'currency', 'service_outer_id']);
            $table->dropColumn('service_outer_id');
        });
    }
};
