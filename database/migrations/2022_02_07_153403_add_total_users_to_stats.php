<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalUsersToStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_stats', function (Blueprint $table) {
            //
            $table->integer('total_users')->default(0);
        });

        Schema::table('viber_stats', function (Blueprint $table) {
            //
            $table->integer('total_users')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_stats', function (Blueprint $table) {
            //
            $table->dropColumn('total_users');
        });

        Schema::table('viber_stats', function (Blueprint $table) {
            //
            $table->dropColumn('total_users');
        });
    }
}
