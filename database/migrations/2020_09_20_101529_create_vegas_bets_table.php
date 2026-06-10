<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVegasBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vegas_bets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('opid');
            $table->string('session_id');
            $table->string('round_id');
            $table->string('game_id');
            $table->string('user');
            $table->integer('bonus_id')->nullable();
            $table->integer('bet')->default(0);
            $table->integer('win')->default(0);
            $table->bigInteger('bet_id')->nullable();
            $table->integer('currency')->default(0);
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
        Schema::dropIfExists('vegas_bets');
    }
}
