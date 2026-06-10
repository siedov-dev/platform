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
        Schema::create('ship_barrel_items', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->integer('amount')->default(1);
            $table->text('params')->nullable();
            $table->text('games')->nullable();
            $table->integer('chance')->default(0);
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
        Schema::dropIfExists('ship_barrel_items');
    }
};
