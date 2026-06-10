<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaySystemsConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_systems_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('system_id');
            $table->string('param_name');
            $table->string('param_key');
            $table->string('param_value');
            $table->timestamps();
        });

        Schema::table('pay_systems_configs', function (Blueprint $table) {
            $table->index('system_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_systems_configs');
    }
}
