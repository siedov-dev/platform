<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSessionFieldToVegasWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->string('session_id')->nullable();
        });

        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->index(['user_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'session_id']);
        });

        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });
    }
}
