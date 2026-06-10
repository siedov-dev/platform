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
        Schema::create('user_task_param_games', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_params_id')->unsigned();
            $table->string('cid');
            $table->timestamps();

            $table->foreign('task_params_id')->on('user_task_params')->references('id')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_task_param_games');
    }
};
