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
        Schema::table('payout_transactions', function (Blueprint $table) {
            //
            $table->bigInteger('method_id')->nullable()->unsigned();

//            $table->foreign('method_id', 'method_id_idx')->references('id')->on('payment_systems')->onDelete('SET NULL');
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
//            $table->dropForeign('method_id_idx');
            $table->dropColumn('method_id');
        });
    }
};
