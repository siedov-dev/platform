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
        Schema::create('bet_by_bet_slips', function (Blueprint $table) {
            $table->id();
            $table->decimal('timestamp', 16, 6);
            $table->string("player_id");
            $table->string("operator_id");
            $table->string("operator_brand_id");
            $table->bigInteger("ext_player_id")->unsigned();
            $table->string("currency", 10);
            $table->string("type");
            $table->bigInteger("sum");
            $table->string("k");
            $table->boolean("is_quick_bet");
            $table->boolean("accept_odds_change");
            $table->text("bets");
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
        Schema::dropIfExists('bet_by_bet_slips');
    }
};
