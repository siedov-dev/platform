<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToApplicationForPaymentTable extends Migration
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
            $table->integer('piastrix_id')->default(0);
            $table->string('error')->default('');
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
            $table->dropColumn('piastrix_id');
            $table->dropColumn('error');
        });
    }
}
