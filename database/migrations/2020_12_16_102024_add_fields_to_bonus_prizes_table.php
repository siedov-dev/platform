<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToBonusPrizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_prizes', function (Blueprint $table) {
            //
            $table->string('name');
            $table->string('info_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_prizes', function (Blueprint $table) {
            //
            $table->dropColumn('name');
            $table->dropColumn('info_url');
        });
    }
}
