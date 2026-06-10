<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusGamesFieldsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            $table->mediumText('allowed_games')->nullable();
        });
        Schema::table('bonus_patterns', function (Blueprint $table) {
            $table->mediumText('allowed_games')->nullable();
        });
        Schema::table('bonus_events', function (Blueprint $table) {
            $table->mediumText('allowed_games')->nullable();
        });
        Schema::table('bonus_group_bonuses', function (Blueprint $table) {
            $table->mediumText('allowed_games')->nullable();
        });
        Schema::table('bonus_schedule', function (Blueprint $table) {
            $table->mediumText('allowed_games')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            $table->dropColumn('allowed_games');
        });
        Schema::table('bonus_patterns', function (Blueprint $table) {
            $table->dropColumn('allowed_games');
        });
        Schema::table('bonus_events', function (Blueprint $table) {
            $table->dropColumn('allowed_games');
        });
        Schema::table('bonus_group_bonuses', function (Blueprint $table) {
            $table->dropColumn('allowed_games');
        });
        Schema::table('bonus_schedule', function (Blueprint $table) {
            $table->dropColumn('allowed_games');
        });
    }
}
