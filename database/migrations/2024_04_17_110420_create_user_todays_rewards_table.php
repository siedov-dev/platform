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
        Schema::create('user_todays_rewards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('task_id');
            $table->integer('amount')->default(1);
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->text('params')->nullable();
            $table->text('allowed_games')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_todays_rewards');
    }
};
