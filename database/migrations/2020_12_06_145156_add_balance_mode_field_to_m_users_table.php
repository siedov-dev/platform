<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBalanceModeFieldToMUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_users', function (Blueprint $table) {
            //
            $table->integer('balance_mode')->default(0)->comment('0 - main balance, 1 - bonus balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_users', function (Blueprint $table) {
            //
            $table->dropColumn('balance_mode');
        });
    }
}
