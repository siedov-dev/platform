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
        Schema::create('landing_games', function (Blueprint $table) {
            $table->id();
            $table->string('type', 15);
            $table->string('lang', 3);
            $table->string('cid');
            $table->timestamps();

            $table->index(['type', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landing_games');
    }
};
