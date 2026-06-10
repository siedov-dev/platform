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
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->integer('promo_type')->default(0)->after('id');
            $table->integer('slc_type')->default(0);
            $table->integer('amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->dropColumn('amount');
            $table->dropColumn('slc_type');
            $table->dropColumn('promo_type');
        });
    }
};
