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
        Schema::create('game_configs', function (Blueprint $table) {
            $table->id();
            $table->string('cid');
            $table->string('system');
            $table->string('group')->nullable();
            $table->string('game_id');
            $table->string('name')->nullable();
            $table->string('img_url')->nullable();
            $table->string('category')->nullable()->default('slots');
            $table->text('origin_data')->nullable();
            $table->string('landing_category')->nullable();
            $table->string('landing_group')->nullable();

            $table->index('cid');
            $table->index(['system', 'group', 'game_id']);
            $table->index('category');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_configs');
    }
};
