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
        Schema::create('slc_slot_machine_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('symbol_1')->nullable();
            $table->string('symbol_2')->nullable();
            $table->string('symbol_3')->nullable();
            $table->integer('slc_reward')->default(0);
            $table->timestamps();

            $table->index(['symbol_1', 'symbol_2', 'symbol_3']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_slot_machine_combinations');
    }
};
