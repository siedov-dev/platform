<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBetLimitsColumnsToTournamentGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournament_games', function (Blueprint $table) {
            $table->float('max_bet')->default(0)->after('spins_limit');
            $table->float('min_bet')->default(0)->after('max_bet');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournament_games', function (Blueprint $table) {
            $table->dropColumn('max_bet');
            $table->dropColumn('min_bet');
        });
    }
}
