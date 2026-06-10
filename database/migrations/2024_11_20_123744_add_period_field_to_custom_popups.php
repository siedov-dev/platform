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
        Schema::table('user_popup_schedules', function (Blueprint $table) {
            //
            $table->boolean('repeatable')->default(false);
            $table->string('repeat_period')->default('');
            $table->integer('repeat_times')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_popup_schedules', function (Blueprint $table) {
            //
            $table->dropColumn('repeat_times');
            $table->dropColumn('repeat_period');
            $table->dropColumn('repeatable');
        });
    }
};
