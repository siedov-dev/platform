<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type');
            $table->string('name')->default('');
            $table->integer('status')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('prize_places')->default(3);
            $table->integer('win_prc')->default(60);
            $table->integer('game_type');
            $table->string('game_params');
            $table->float('bank')->default(0);
            $table->float('buyin_cost')->default(0);
            $table->float('rebuy_cost')->default(0);
            $table->integer('rebuy_count')->default(0)->comment('-1 unlimited');
            $table->integer('buyin_bank_prc')->default(0)->comment('% of buyin sum which will be included to bank (10 = 10%)');
            $table->integer('rebuy_bank_prc')->default(0)->comment('% of rebuy sum which will be included to bank (10 = 10%)');
            $table->longText('player_list')->nullable()->comment('json array players id\'s allowed to participate, null - all allowed');
            $table->longText('game_list')->nullable()->comment('participating in tournament game id\'s, null - all allowed ');
            $table->string('params');
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
        Schema::dropIfExists('tournaments');
    }
}
