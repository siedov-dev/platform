<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusSchedulePromoFiled extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('bonus_schedule', function (Blueprint $table) {
            $table->string('promocode', 32)->nullable()->after('wager_koef');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('bonus_schedule', function (Blueprint $table) {
            $table->dropColumn('promocode');
        });
    }
}
