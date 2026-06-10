<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ranks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('rank_level')->default(0);
            $table->decimal('start_value', 10, 2)->default(0);
            $table->decimal('end_value', 10, 2)->default(0);
            $table->integer('deposit_from')->default(0);
            $table->integer('spins')->default(0);
            $table->integer('bet_limit')->default(0);
            $table->integer('wager')->default(0);
            $table->integer('win_limit')->default(0);
            $table->integer('cashback')->default(0);
            $table->integer('weekly_cashback')->default(5);
            $table->integer('wager_diminution')->default(0);
            $table->boolean('vip_events')->default(false);
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
        Schema::dropIfExists('user_ranks');
    }
}
