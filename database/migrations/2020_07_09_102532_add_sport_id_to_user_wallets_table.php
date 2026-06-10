<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSportIdToUserWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            //
            $table->string('sport_id')->nullable();
            $table->string('sport_token')->nullable();
            $table->string('sport_access_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            //
            $table->dropColumn('sport_id');
            $table->dropColumn('sport_token');
            $table->dropColumn('sport_access_key');
        });
    }
}
