<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusWagerPrcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_wager_prcs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('system');
            $table->string('provider')->nullable();
            $table->string('game_id');
            $table->integer('prc')->default(10);
            $table->timestamps();

            $table->index(['provider', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_wager_prcs');
    }
}
