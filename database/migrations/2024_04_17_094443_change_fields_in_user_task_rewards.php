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
        Schema::table('user_task_rewards', function (Blueprint $table) {
            //
            $table->bigInteger('task_id');
            $table->integer('max_amount')->default(1);
            $table->integer('step_amount')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_task_rewards', function (Blueprint $table) {
            //
            $table->dropColumn('step_amount');
            $table->dropColumn('max_amount');
            $table->dropColumn('task_id');
        });
    }
};
