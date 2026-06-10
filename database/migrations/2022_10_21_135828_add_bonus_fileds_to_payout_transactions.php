<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusFiledsToPayoutTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payout_transactions', function (Blueprint $table) {
            //
            $table->integer('system_id')->nullable();
            $table->string('method', 100)->nullable();
            $table->integer('bonus_id')->nullable();
            $table->string('bonus_system', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payout_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('bonus_system');
            $table->dropColumn('bonus_id');
            $table->dropColumn('method');
            $table->dropColumn('system_id');
        });
    }
}
