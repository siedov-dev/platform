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
        Schema::create('daily_bonuses', function (Blueprint $table) {
            $table->id();
            $table->integer('day');
            $table->integer('currency');
            $table->integer('type');
            $table->text('params')->nullable();
            $table->mediumText('games')->nullable();
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
        Schema::dropIfExists('daily_bonuses');
    }
};
