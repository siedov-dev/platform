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
        Schema::create('custom_popups', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('img');
            $table->longText('locale_texts');
            $table->string('button_type');
            $table->string('button_url');
            $table->boolean('show_no_more')->default(false);
            $table->timestamps();

            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_popups');
    }
};
