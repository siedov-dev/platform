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
        Schema::table('slc_shop_items', function (Blueprint $table) {
            $table->string('img')->nullable();
            $table->mediumText('descr')->nullable();
        });

        Schema::table('slc_user_shop_items', function (Blueprint $table) {
            $table->string('img')->nullable();
            $table->mediumText('descr')->nullable();
        });

        Schema::table('slc_chest_items', function (Blueprint $table) {
            $table->string('img')->nullable();
            $table->mediumText('descr')->nullable();
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
            $table->dropColumn('descr');
            $table->dropColumn('img');
        });

        Schema::table('slc_user_shop_items', function (Blueprint $table) {
            $table->dropColumn('descr');
            $table->dropColumn('img');
        });

        Schema::table('slc_shop_items', function (Blueprint $table) {
            $table->dropColumn('descr');
            $table->dropColumn('img');
        });
    }
};
