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
        Schema::create('bonus_segment_user_bonus', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_bonus_id')->nullable();
            $table->unsignedBigInteger('bonus_segment_id')->nullable();

            $table->foreign('user_bonus_id')->references('id')
                ->on('users_bonuses')->onDelete('cascade');
            $table->foreign('bonus_segment_id')->references('id')
                ->on('bonus_segments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_segment_user_bonus');
    }
};
