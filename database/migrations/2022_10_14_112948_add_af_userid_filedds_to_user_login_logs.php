<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAfUseridFileddsToUserLoginLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            //
            $table->integer('cookie_uid')->nullable();
            $table->integer('storage_uid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            //
            $table->dropColumn('storage_uid');
            $table->dropColumn('cookie_uid');
        });
    }
}
