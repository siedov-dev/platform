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
        Schema::create('popups_global_showns', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('popup_id');
            $table->bigInteger('user_id');
            $table->timestamps();

            $table->index(['popup_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('popups_global_showns');
    }
};
