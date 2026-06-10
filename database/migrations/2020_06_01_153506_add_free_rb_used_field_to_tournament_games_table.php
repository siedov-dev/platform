<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeRbUsedFieldToTournamentGamesTable extends Migration
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
            $table->integer('free_rb_used')->nullable()->default(0)->after('game_count');
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
            $table->dropColumn('free_rb_used');
        });
    }
}
