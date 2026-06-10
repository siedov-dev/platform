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
        Schema::create('bonus_promocode_bonus_segment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonus_promocode_id')->nullable();
            $table->unsignedBigInteger('bonus_segment_id')->nullable();

            $table->foreign('bonus_promocode_id')->references('id')
                ->on('bonus_promocodes')->onDelete('cascade');
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
        Schema::dropIfExists('bonus_promocode_bonus_segment');
    }
};
