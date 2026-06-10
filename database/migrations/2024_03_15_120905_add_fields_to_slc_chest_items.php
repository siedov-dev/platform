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
        Schema::table('slc_chest_items', function (Blueprint $table) {
            //
            $table->integer('max_amount')->default(0);
            $table->integer('multiplier')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_chest_items', function (Blueprint $table) {
            //
            $table->dropColumn('multiplier');
            $table->dropColumn('max_amount');
        });
    }
};
