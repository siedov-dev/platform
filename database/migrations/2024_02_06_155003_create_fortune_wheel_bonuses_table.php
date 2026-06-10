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
        Schema::create('fortune_wheel_bonuses', function (Blueprint $table) {
            $table->id();
            $table->integer('prize_type')->default(0);
            $table->integer('bonus_type');
            $table->string('bonus_title')->nullable();
            $table->text('bonus_params');
            $table->text('bonus_games')->nullable();
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
        Schema::dropIfExists('fortune_wheel_bonuses');
    }
};
