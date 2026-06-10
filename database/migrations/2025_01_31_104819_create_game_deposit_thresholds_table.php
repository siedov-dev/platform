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
        Schema::create('game_deposit_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10);
            $table->decimal('threshold', 10,4);

            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_deposit_thresholds');
    }
};
