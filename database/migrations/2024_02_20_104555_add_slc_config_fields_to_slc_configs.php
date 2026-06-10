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
        Schema::table('slc_configs', function (Blueprint $table) {
            $table->integer('jackpot_prc')->default(10);
            $table->integer('start_jackpot')->default(100);
            $table->integer('max_jackpot')->default(0);
            $table->integer('random_gen')->default(0);
            $table->integer('wheel_respin')->default(2);
            $table->float('jackpot_inc')->default(1);
            $table->integer('jackpot_reset')->default(100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_configs', function (Blueprint $table) {
            $table->dropColumn('jackpot_prc');
            $table->dropColumn('start_jackpot');
            $table->dropColumn('max_jackpot');
            $table->dropColumn('random_gen');
            $table->dropColumn('wheel_respin');
            $table->dropColumn('jackpot_inc');
            $table->dropColumn('jackpot_reset');
        });
    }
};
