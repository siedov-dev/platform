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
        Schema::create('slc_user_achievements', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->bigInteger('achievement_id');
            $table->boolean('rewarded')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'achievement_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_user_achievements');
    }
};
