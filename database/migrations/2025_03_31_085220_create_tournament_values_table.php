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
        Schema::create('tournament_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tournament_id')->unsigned();
            $table->integer('user_id');
            $table->decimal('value', 12, 1)->default(0);
            $table->timestamps();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('CASCADE');
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
        Schema::dropIfExists('tournament_values');
    }
};
