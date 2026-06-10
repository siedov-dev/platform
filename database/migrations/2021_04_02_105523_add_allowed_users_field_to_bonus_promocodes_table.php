<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowedUsersFieldToBonusPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->mediumText('allowed_users')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->dropColumn('allowed_users');
        });
    }
}
