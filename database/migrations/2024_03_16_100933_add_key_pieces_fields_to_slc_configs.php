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
        Schema::table('slc_configs', function (Blueprint $table) {
            //
            $table->integer('small_key_required')->default(100);
            $table->integer('middle_key_required')->default(100);
            $table->integer('big_key_required')->default(100);
            $table->integer('bermuda_key_required')->default(100);
            $table->integer('lost_key_required')->default(100);
            $table->integer('black_key_required')->default(100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_configs', function (Blueprint $table) {
            //
            $table->dropColumn('black_key_required');
            $table->dropColumn('lost_key_required');
            $table->dropColumn('bermuda_key_required');
            $table->dropColumn('big_key_required');
            $table->dropColumn('middle_key_required');
            $table->dropColumn('small_key_required');
        });
    }
};
