<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelegramStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('stat_date');
            $table->integer('join')->default(0);
            $table->integer('connected')->default(0);
            $table->integer('commands')->default(0);
            $table->integer('messages')->default(0);
            $table->integer('images')->default(0);
            $table->integer('promos_created')->default(0);
            $table->integer('blocked')->default(0);
            $table->integer('errors')->default(0);
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
        Schema::dropIfExists('telegram_stats');
    }
}
