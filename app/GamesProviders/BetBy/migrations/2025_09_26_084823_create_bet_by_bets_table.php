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
        Schema::create('bet_by_bets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("amount");
            $table->string("currency");
            $table->bigInteger("player_id")->unsigned();
            $table->string("session_id");
            $table->bigInteger("bonus_id")->unsigned();
            $table->string("bonus_type");
            $table->bigInteger("transaction_id")->unsigned();
            $table->bigInteger("betslip_id")->unsigned();
            $table->bigInteger("potential_win");
            $table->bigInteger("potential_comboboost_win");
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
        Schema::dropIfExists('bet_by_bets');
    }
};
