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
        Schema::table('application_for_payment', function (Blueprint $table) {
            //
            $table->bigInteger('method_id')->nullable()->unsigned();
            $table->bigInteger('system_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_for_payment', function (Blueprint $table) {
            //
            $table->dropColumn('method_id');
            $table->dropColumn('system_id');
        });
    }
};
