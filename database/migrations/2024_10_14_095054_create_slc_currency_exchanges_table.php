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
        Schema::create('slc_currency_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('currency');
            $table->integer('slc_amount');
            $table->decimal('currency_amount', 16, 4)->default(0);
            $table->integer('prc');
            $table->string('img');
            $table->timestamps();

            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_currency_exchanges');
    }
};
