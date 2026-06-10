<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOuterIdFieldToApplicationForPayment extends Migration
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
            $table->string('outer_id')->nullable();
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
            $table->dropColumn('outer_id');
        });
    }
}
