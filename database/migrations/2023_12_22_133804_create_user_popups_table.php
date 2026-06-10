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
        Schema::create('user_popups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('priority')->default(0);
            $table->mediumText('content')->nullable();
            $table->string('category')->nullable();
            $table->dateTime('show_at')->nullable();
            $table->dateTime('actual_to')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_popups');
    }
};
