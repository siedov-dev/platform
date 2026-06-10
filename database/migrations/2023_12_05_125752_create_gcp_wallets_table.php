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
        Schema::create('soft_swiss_wallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('amount')->default(0);
            $table->integer('digits')->default(2);
            $table->integer('currency');
            $table->string('game_currency', 10)->nullable();
            $table->string('currency_name', 10)->nullable();
            $table->bigInteger('bonus_id')->nullable();
            $table->string('token')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'currency', 'currency_name']);
            $table->index('token');
            $table->index('session_id');
            $table->index('bonus_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soft_swiss_wallets');
    }
};
