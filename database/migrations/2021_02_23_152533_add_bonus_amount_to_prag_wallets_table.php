<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusAmountToPragWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prag_wallets', function (Blueprint $table) {
            //
            $table->decimal('bonus_amount', 10, 4)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prag_wallets', function (Blueprint $table) {
            //
            $table->dropColumn('bonus_amount');
        });
    }
}
