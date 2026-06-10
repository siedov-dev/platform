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
        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->string('game_currency')->after('currency')->nullable();
        });
        Schema::table('prag_wallets', function (Blueprint $table) {
            $table->string('game_currency')->after('currency')->nullable();
        });
        Schema::table('slerg_wallets', function (Blueprint $table) {
            $table->string('game_currency')->after('currency')->nullable();
        });
        Schema::table('qt_wallets', function (Blueprint $table) {
            $table->string('game_currency')->after('currency')->nullable();
        });
        Schema::table('user_currencies', function (Blueprint $table) {
            $table->string('game_currency')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_currencies', function (Blueprint $table) {
            $table->dropColumn('game_currency');
        });
        Schema::table('qt_wallets', function (Blueprint $table) {
            $table->dropColumn('game_currency');
        });
        Schema::table('slerg_wallets', function (Blueprint $table) {
            $table->dropColumn('game_currency');
        });
        Schema::table('prag_wallets', function (Blueprint $table) {
            $table->dropColumn('game_currency');
        });
        Schema::table('vegas_wallets', function (Blueprint $table) {
            $table->dropColumn('game_currency');
        });
    }
};
