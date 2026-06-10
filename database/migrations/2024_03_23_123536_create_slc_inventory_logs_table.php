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
        Schema::create('slc_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('type');
            $table->integer('amount');
            $table->integer('bonus_type')->nullable();
            $table->text('params')->nullable();
            $table->text('games')->nullable();
            $table->integer('source_id')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_inventory_logs');
    }
};
