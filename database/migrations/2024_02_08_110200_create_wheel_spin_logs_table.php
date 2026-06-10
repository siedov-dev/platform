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
        Schema::create('wheel_spin_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->bigInteger('whee_prize_id');
            $table->bigInteger('bonus_id')->nullable();
            $table->integer('bonus_type')->nullable();
            $table->string('bonus_title')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wheel_spin_logs');
    }
};
