<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeeFieldToPayoutTransactionsTable extends Migration
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
            $table->float('fee')->default(0)->after('sum');
            $table->float('total_sum')->default(0)->after('fee');
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
            $table->dropColumn('fee');
            $table->dropColumn('total_sum');
        });
    }
}
