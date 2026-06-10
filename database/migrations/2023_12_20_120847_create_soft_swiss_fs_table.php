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
        Schema::create('soft_swiss_fs', function (Blueprint $table) {
            $table->id();
            $table->string('spins_id');
            $table->bigInteger('user_id');
            $table->string('currency', 10)->nullable();
            $table->string('status')->default('active');
            $table->integer('amount');
            $table->timestamps();

            $table->index(['spins_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soft_swiss_fs');
    }
};
