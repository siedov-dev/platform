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
        Schema::create('bonus_multi_days_params', function (Blueprint $table) {
            $table->id();
            $table->integer('day');
            $table->string('game_cid');
            $table->integer('spins');
            $table->integer('wager');
            $table->text('params')->nullable();
            $table->timestamps();

            $table->index('day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_multi_days_params');
    }
};
