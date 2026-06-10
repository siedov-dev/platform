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
        Schema::table('customer_io_users', function (Blueprint $table) {
            //
            $table->integer('wb_state')->default(0);
            $table->integer('wb')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_io_users', function (Blueprint $table) {
            //
            $table->dropColumn('wb');
            $table->dropColumn('wb_state');
        });
    }
};
