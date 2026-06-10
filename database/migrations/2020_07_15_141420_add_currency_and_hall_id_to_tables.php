<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyAndHallIdToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->integer('hall_id')->default(237);
            $table->integer('currency')->default(1);
        });

        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->integer('hall_id')->default(237);
            $table->integer('currency')->default(1);
        });

        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->integer('hall_id')->default(237);
            $table->integer('currency')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->dropColumn('hall_id');
            $table->dropColumn('currency');
        });

        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->dropColumn('hall_id');
            $table->dropColumn('currency');
        });

        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->dropColumn('hall_id');
            $table->dropColumn('currency');
        });
    }
}
