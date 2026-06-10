<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxLimitFieldToBonusScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            $table->integer('max_limit')->default(0)->after('wager_koef');
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
            $table->dropColumn('max_limit');
        });
    }
}
