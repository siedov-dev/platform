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
        Schema::create('message_blank_schedules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('blank_id');
            $table->mediumText('ids')->nullable();
            $table->text('blade_data')->nullable();
            $table->integer('status')->default(0);
            $table->integer('total')->default(0);
            $table->integer('sent')->default(0);
            $table->integer('errors')->default(0);
            $table->string('method')->nullable();
            $table->string('last_error')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index(['blank_id', 'status', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_blank_schedules');
    }
};
