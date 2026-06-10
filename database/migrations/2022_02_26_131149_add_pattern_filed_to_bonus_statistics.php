<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatternFiledToBonusStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_statistics', function (Blueprint $table) {
            //
            $table->integer('pattern_id')->nullable()->after('bonus_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_statistics', function (Blueprint $table) {
            //
            $table->dropColumn('pattern_id');
        });
    }
}
