<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusDisabledGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_disabled_games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bonus_type');
            $table->integer('bonus_pattern')->nullable();
            $table->string('disabled_cid');
            $table->timestamps();

            $table->index(['bonus_type', 'disabled_cid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_disabled_games');
    }
}
