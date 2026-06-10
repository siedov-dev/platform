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
        Schema::table('slc_achievements', function (Blueprint $table) {
            //
            $table->integer('item_type')->nullable();
            $table->integer('amount')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_achievements', function (Blueprint $table) {
            //
            $table->dropColumn('item_type');
            $table->dropColumn('amount');
        });
    }
};
