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
        Schema::create('slc_achievement_rewards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ach_id')->unsigned();
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->integer('amount')->default(1);
            $table->text('params')->nullable();
            $table->text('games')->nullable();
            $table->timestamps();

            $table->foreign('ach_id')->on('slc_achievements')->references('id')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_achievement_rewards');
    }
};
