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
        Schema::create('game_group_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('system_id');
            $table->string('group_id');
            $table->integer('priority')->default(0);

            $table->index(['system_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_group_priorities');
    }
};
