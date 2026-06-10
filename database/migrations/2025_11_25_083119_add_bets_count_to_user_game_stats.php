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
        Schema::table('user_game_stats', function (Blueprint $table) {
            $table->integer('bets_count')->default(0);
            $table->integer('bonus_bets_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_game_stats', function (Blueprint $table) {
            $table->dropColumn('bets_count');
            $table->dropColumn('bonus_bets_count');
        });
    }
};
