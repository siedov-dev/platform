<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyFiledToVegasWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vegas_wallets', function (Blueprint $table) {
            //
            $table->integer('currency')->default(1)->after('money');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vegas_wallets', function (Blueprint $table) {
            //
            $table->dropColumn('currency');
        });
    }
}
