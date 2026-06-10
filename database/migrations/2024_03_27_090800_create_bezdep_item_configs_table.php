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
        Schema::create('bezdep_item_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('bonus_type');
            $table->integer('active_days')->default(6);
            $table->text('params');
            $table->text('games')->nullable();
            $table->string('title')->nullable();
            $table->string('img_url')->nullable();
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
        Schema::dropIfExists('bezdep_item_configs');
    }
};
