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
        Schema::create('slc_user_shop_items', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->bigInteger('shop_item_id');
            $table->integer('amount')->default(0);
            $table->dateTime('active_to')->nullable();
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
        Schema::dropIfExists('slc_user_shop_items');
    }
};
