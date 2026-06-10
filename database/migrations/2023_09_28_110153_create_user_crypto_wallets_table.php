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
        Schema::create('user_crypto_wallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('currency', 10);
            $table->string('system_id');
            $table->mediumText('address');
            $table->timestamps();

            $table->index(['user_id', 'currency', 'system_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_crypto_wallets');
    }
};
