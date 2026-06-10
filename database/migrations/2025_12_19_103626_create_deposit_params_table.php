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
        Schema::create('deposit_params', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10);
            $table->decimal('wager_drop_amount', 10, 4);
            $table->integer('wager');
            $table->timestamps();

            $table->unique(['currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_params');
    }
};
