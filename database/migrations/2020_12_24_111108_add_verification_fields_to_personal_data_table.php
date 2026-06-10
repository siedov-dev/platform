<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerificationFieldsToPersonalDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_data', function (Blueprint $table) {
            //
            $table->string('patr')->nullable();
            $table->string('passport')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_data', function (Blueprint $table) {
            //
            $table->dropColumn('patr');
            $table->dropColumn('passport');
            $table->dropColumn('country');
            $table->dropColumn('region');
            $table->dropColumn('city');
            $table->dropColumn('address');
            $table->dropColumn('message');
        });
    }
}
