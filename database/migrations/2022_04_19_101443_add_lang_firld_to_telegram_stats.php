<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLangFirldToTelegramStats extends Migration
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
            $table->string('lang', 5)->default('ru')->after('stat_date');
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
            $table->dropColumn('lang');
        });
    }
}
