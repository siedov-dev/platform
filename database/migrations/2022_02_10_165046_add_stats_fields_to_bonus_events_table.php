<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatsFieldsToBonusEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->integer('creator_type')->default(0);
            $table->integer('creator_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->dropColumn('creator_type');
            $table->dropColumn('creator_id');
        });
    }
}
