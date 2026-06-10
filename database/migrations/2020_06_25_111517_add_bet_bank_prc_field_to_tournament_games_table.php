<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBetBankPrcFieldToTournamentGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournament_games', function (Blueprint $table) {
            //
            $table->integer('bet_bank_prc')->default(0)->after('min_bet');
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
            //
            $table->dropColumn('bet_bank_prc');
        });
    }
}
