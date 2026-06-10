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
        Schema::table('tournament_prizes', function (Blueprint $table) {
            //
            $table->string('container_img');
            $table->mediumText('container_texts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournament_prizes', function (Blueprint $table) {
            //
            $table->dropColumn('container_texts');
            $table->dropColumn('container_img');
        });
    }
};
