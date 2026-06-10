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
        Schema::create('slc_daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->integer('day');
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->integer('amount')->nullable();
            $table->text('params')->nullable();
            $table->text('allowed_games')->nullable();
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
        Schema::dropIfExists('slc_daily_rewards');
    }
};
