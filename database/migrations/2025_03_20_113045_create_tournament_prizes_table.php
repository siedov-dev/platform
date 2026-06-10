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
        Schema::create('tournament_prizes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id');
            $table->integer('place');
            $table->string('name');
            $table->string('img');
            $table->string('currency_amounts')->nullable();
            $table->integer('item_type')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('bonus_type')->default(0);
            $table->mediumText('bonus_params')->nullable();
            $table->mediumText('bonus_games')->nullable();

            $table->index(['group_id', 'place']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_prizes');
    }
};
