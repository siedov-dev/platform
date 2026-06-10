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
        Schema::create('promo_items', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->string('action_url')->nullable();
            $table->string('img')->nullable();
            $table->mediumText('control_texts')->nullable();
            $table->longText('descriptions')->nullable();
            $table->boolean('has_description')->default(false);
            $table->boolean('show_timer')->default(false);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('active')->default(false);
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
        Schema::dropIfExists('promo_items');
    }
};
