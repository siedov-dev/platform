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
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->dropColumn('vegas_game_list');
            $table->dropColumn('pragplay_games');
            $table->dropColumn('sleg_games');

            $table->boolean('req_confirm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->dropColumn('req_confirm');

            $table->string('sleg_games');
            $table->string('pragplay_games');
            $table->string('vegas_game_list');
        });
    }
};
