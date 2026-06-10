<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePragGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prag_games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->integer('hall_id');
            $table->string('session_id')->nullable();
            $table->string('game_id');
            $table->decimal('login_balance', 10,2)->default(0);
            $table->decimal('logout_balance', 10,2)->default(0);
            $table->timestamp('login_date')->nullable();
            $table->timestamp('logout_date')->nullable();
            $table->timestamp('last_active_date')->nullable();            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prag_games');
    }
}
