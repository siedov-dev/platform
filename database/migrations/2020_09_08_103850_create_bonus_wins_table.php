<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusWinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_wins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('hall_id')->nullable();
            $table->string('email');
            $table->integer('currency');
            $table->string('nickname');
            $table->string('game_id');
            $table->string('game_name');
            $table->decimal('win', 10, 2);
            $table->string('provider')->default('');
            $table->string('system');
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
        Schema::dropIfExists('bonus_wins');
    }
}
