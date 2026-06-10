<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddViberFieldsToBonusSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            //
            $table->integer('send_viber')->after('send_tg')->default(0);
            $table->bigInteger('blank_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            //
            $table->dropColumn('send_viber');
            $table->dropColumn('blank_id');
        });
    }
}
