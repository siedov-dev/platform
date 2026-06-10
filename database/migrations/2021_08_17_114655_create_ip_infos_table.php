<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIpInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_info', function (Blueprint $table) {
            $table->string('ip', 50)->primary();
            $table->string('continent')->default('');
            $table->string('country')->default('');
            $table->string('country_code')->default('');
            $table->string('region')->default('');
            $table->string('city')->default('');
            $table->string('lat')->default('');
            $table->string('long')->default('');
            $table->string('currency')->default('');
            $table->string('isp')->default('');
            $table->string('org')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_info');
    }
}
