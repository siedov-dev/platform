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
        Schema::create('api_lx_pragmatic_game_bets', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->string('currency', 10);
            $table->decimal('min_bet', 16, 4);
            $table->integer('max_bet_level');
            $table->timestamps();

            $table->index(['game_id', 'currency'], 'game_currency_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_lx_pragmatic_game_bets');
    }
};
