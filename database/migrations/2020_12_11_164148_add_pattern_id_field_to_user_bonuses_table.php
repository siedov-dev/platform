<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatternIdFieldToUserBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->integer('pattern_id')->nullable();
            $table->integer('pattern_status')->default(0);
            $table->string('bonus_prizes')->nullable();
            $table->string('given_prizes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->dropColumn('pattern_id');
            $table->dropColumn('pattern_status');
            $table->dropColumn('bonus_prizes');
            $table->dropColumn('given_prizes');
        });
    }
}
