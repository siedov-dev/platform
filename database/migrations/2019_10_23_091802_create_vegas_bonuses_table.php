<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVegasBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vegas_bonuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('money');
            $table->integer('type');
            $table->integer('status')->default(0);
            $table->integer('free_spins')->default(0);
            $table->integer('wager_koef')->default(0);
            $table->integer('wager_start')->default(0);
            $table->integer('wager_target')->default(0);
            $table->integer('hours_active');
            $table->double('total_bet')->default(0);
            $table->double('total_win')->default(0);
            $table->integer('total_spins')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('info');
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
        Schema::dropIfExists('vegas_bonuses');
    }
}
