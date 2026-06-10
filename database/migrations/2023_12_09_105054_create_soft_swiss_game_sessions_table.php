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
        Schema::create('soft_swiss_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('hall_id')->nullable();
            $table->integer('currency')->nullable();
            $table->string('session_id')->nullable();
            $table->string('game_id')->nullable();
            $table->decimal('login_balance', 16, 4)->nullable();
            $table->decimal('logout_balance', 16, 4)->nullable();
            $table->dateTime('login_date')->nullable();
            $table->dateTime('logout_date')->nullable();
            $table->dateTime('last_active_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soft_swiss_game_sessions');
    }
};
