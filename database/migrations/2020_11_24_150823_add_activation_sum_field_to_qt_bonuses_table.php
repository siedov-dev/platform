<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivationSumFieldToQtBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qt_bonuses', function (Blueprint $table) {
            //
            $table->integer('activation_sum')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qt_bonuses', function (Blueprint $table) {
            //
            $table->dropColumn('activation_sum');
        });
    }
}
