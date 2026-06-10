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
            $table->string('o_tr_id')->nullable();
            $table->integer('status')->default(0);
            $table->integer('type')->default(0);

            $table->index('o_tr_id');
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
            $table->dropColumn('type');
            $table->dropColumn('status');
            $table->dropColumn('o_tr_id');
        });
    }
};
