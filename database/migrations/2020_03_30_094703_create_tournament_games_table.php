<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('tournament_id');
            $table->integer('type');
            $table->integer('status');
            $table->integer('game_count')->default(0);
            $table->float('money')->default(0);
            $table->integer('current_spin')->default(0);
            $table->integer('free_spins')->default(0);
            $table->integer('spins_limit')->default(-1);
            $table->float('curr_value')->default(0);
            $table->float('new_value')->default(0);
            $table->string('additional_data')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_games');
    }
}
