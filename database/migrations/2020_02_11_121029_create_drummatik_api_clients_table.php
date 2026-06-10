<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrummatikApiClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drummatik_api_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id');
            $table->string('key');
            $table->integer('hall_id');
            $table->ipAddress('ip')->default('127.0.0.1');
            $table->boolean('seamless_wallet')->default(false);
            $table->string('seamless_endpoint')->nullable();
            $table->string('game_server')->default('http://games.local');
            $table->boolean('check_ip');
            $table->boolean('check_hash');
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
        Schema::dropIfExists('drummatik_api_clients');
    }
}
