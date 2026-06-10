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
        Schema::create('slc_slot_machine_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('drum_0');
            $table->integer('drum_1');
            $table->integer('drum_2');
            $table->string('drum_0_symbol')->nullable();
            $table->string('drum_1_symbol')->nullable();
            $table->string('drum_2_symbol')->nullable();
            $table->integer('reward')->default(0);
            $table->timestamps();

            $table->index('user_id');
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
        Schema::dropIfExists('slc_slot_machine_logs');
    }
};
