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
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->string('tile_img')->default('');
            $table->string('mobile_img')->default('');
            $table->string('desktop_img')->default('');
            $table->string('cabinet_d_img')->default('');
            $table->string('cabinet_m_img')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            //
            $table->dropColumn('tile_img');
            $table->dropColumn('mobile_img');
            $table->dropColumn('desktop_img');
            $table->dropColumn('cabinet_d_img');
            $table->dropColumn('cabinet_m_img');
        });
    }
};
