<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slider_banners', function (Blueprint $table) {
            $table->boolean('time_limited')->default(false);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slider_banners', function (Blueprint $table) {
            //
            $table->dropColumn('end_time');
            $table->dropColumn('start_time');
            $table->dropColumn('time_limited');
        });
    }
};
