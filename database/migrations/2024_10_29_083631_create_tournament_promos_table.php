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
        Schema::create('tournament_promos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('fund');
            $table->string('min_bet');
            $table->integer('min_spins')->default(0);
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('tile_img');
            $table->string('mobile_img');
            $table->string('desktop_img');
            $table->string('cabinet_d_img');
            $table->string('cabinet_m_img');
            $table->mediumText('games')->nullable();
            $table->longText('conditions');
            $table->longText('prizes');
            $table->timestamps();

            $table->index(['start', 'end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_promos');
    }
};
