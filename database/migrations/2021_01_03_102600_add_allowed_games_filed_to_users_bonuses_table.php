<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowedGamesFiledToUsersBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->boolean('games_limited')->default(false);
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
        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->dropColumn('games_limited');
            $table->dropColumn('allowed_games');
        });
    }
}
