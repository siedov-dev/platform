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
        Schema::create('payment_method_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_method_id')->unsigned();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();

            $table->unique(['payment_method_id', 'key']);
            $table->foreign('payment_method_id')->references('id')->on('payment_systems')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_method_data');
    }
};
