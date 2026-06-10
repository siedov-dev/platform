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
            $table->integer('amount')->default(0);
            $table->dropColumn('amount_from');
            $table->dropColumn('amount_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_user_shop_items', function (Blueprint $table) {
            //
            $table->dropColumn('amount');
            $table->integer('amount_from')->default(0);
            $table->integer('amount_to')->default(0);
        });
    }
};
