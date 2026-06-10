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
        Schema::create('user_task_rewards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id');
            $table->integer('task_count');
            $table->integer('type');
            $table->integer('amount');
            $table->integer('bonus_type')->nullable();
            $table->text('params')->nullable();
            $table->text('allowed_games')->nullable();
            $table->timestamps();

            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_task_rewards');
    }
};
