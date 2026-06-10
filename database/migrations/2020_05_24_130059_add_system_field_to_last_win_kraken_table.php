<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemFieldToLastWinKrakenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('last_win_kraken', function (Blueprint $table) {
            //
            $table->string('provider')->nullable();
            $table->string('system')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('last_win_kraken', function (Blueprint $table) {
            //
            $table->dropColumn('provider');
            $table->dropColumn('system');
        });
    }
}
