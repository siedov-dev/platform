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
        Schema::table('bonus_menu_params', function (Blueprint $table) {
            //
            $table->decimal('value1', 14,2)->nullable();
            $table->decimal('value2', 14,2)->nullable();
            $table->decimal('value3', 14,2)->nullable();
            $table->decimal('value4', 14,2)->nullable();
            $table->decimal('value5', 14,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_menu_params', function (Blueprint $table) {
            //
            $table->dropColumn('value5');
            $table->dropColumn('value4');
            $table->dropColumn('value3');
            $table->dropColumn('value2');
            $table->dropColumn('value1');
        });
    }
};
