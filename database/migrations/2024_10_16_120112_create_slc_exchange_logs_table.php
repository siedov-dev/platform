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
        Schema::create('slc_exchange_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('currency', 10);
            $table->integer('slc_amount')->default(0);
            $table->decimal('curr_amount', 16, 4)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_exchange_logs');
    }
};
