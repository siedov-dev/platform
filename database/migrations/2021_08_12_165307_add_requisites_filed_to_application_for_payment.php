<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequisitesFiledToApplicationForPayment extends Migration
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
            $table->text('requisites')->nullable();
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
            $table->dropColumn('requisites');
        });
    }
}
