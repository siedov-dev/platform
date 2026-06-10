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
        Schema::create('cash_back_plus_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('value');
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('m_users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_back_plus_logs');
    }
};
