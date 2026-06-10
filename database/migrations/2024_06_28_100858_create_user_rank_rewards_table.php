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
        Schema::create('user_rank_rewards', function (Blueprint $table) {
            $table->id();
            $table->integer('rank_level');
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->integer('amount')->default(1);
            $table->text('params')->nullable();
            $table->text('allowed_games')->nullable();
            $table->timestamps();

            $table->index('rank_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_rank_rewards');
    }
};
