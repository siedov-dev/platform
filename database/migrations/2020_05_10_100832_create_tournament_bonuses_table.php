<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_bonuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tournament_bonus_group_id');
            $table->integer('position')->comment('position in tournament table');
            $table->integer('bonus_type')->comment('bonus type id');
            $table->string('params')->comment('bonus create parameters');
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
        Schema::dropIfExists('tournament_bonuses');
    }
}
