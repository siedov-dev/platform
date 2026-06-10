<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToBonusScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            //
            $table->integer('send_tg')->default(0)->after('send_email');
            $table->text('tg_message')->nullable();
            $table->mediumText('tg_media_type')->nullable();
            $table->mediumText('tg_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            //
            $table->dropColumn('send_tg');
            $table->dropColumn('tg_message');
            $table->dropColumn('tg_media_type');
            $table->dropColumn('tg_image');
        });
    }
}
