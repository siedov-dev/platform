<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLoginLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('ip')->default('');
            $table->string('browser')->default('');
            $table->string('browser_version')->default('');
            $table->string('platform')->default('');
            $table->string('device_name')->default('');
            $table->string('device_type')->default('');
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
        Schema::dropIfExists('user_login_logs');
    }
}
