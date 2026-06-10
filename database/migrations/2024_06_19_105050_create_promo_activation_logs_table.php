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
        Schema::create('promo_activation_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('promo_id')->nullable();
            $table->boolean('success')->default(false);
            $table->boolean('registration')->default(false);
            $table->string('promocode')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'promo_id', 'success']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_activation_logs');
    }
};
