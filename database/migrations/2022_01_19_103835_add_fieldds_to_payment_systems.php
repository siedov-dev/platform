<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFielddsToPaymentSystems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            //
            $table->integer('priority')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('has_reqs')->default(false)->comment('has additional requirements');
            $table->boolean('reqs_enabled')->default(false);
            $table->integer('traffic')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            //
            $table->dropColumn('priority');
            $table->dropColumn('hidden');
            $table->dropColumn('has_reqs');
            $table->dropColumn('reqs_enabled');
            $table->dropColumn('traffic');
        });
    }
}
