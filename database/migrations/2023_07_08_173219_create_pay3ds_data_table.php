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
        Schema::create('pay3ds_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transaction_id');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->text('params')->nullable();
            $table->string('md')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay3ds_data');
    }
};
