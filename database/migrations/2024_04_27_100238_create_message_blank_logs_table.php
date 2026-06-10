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
        Schema::create('message_blank_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('schedule_id');
            $table->bigInteger('user_id');
            $table->string('method')->nullable();
            $table->string('response')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_blank_logs');
    }
};
