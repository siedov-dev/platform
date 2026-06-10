<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowedGamesToTournamentBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournament_bonuses', function (Blueprint $table) {
            //
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
        Schema::table('tournament_bonuses', function (Blueprint $table) {
            //
            $table->dropColumn('allowed_games');
        });
    }
}
