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
        Schema::table('customer_io_users', function (Blueprint $table) {
            //
            $table->string('top_game_id')->nullable();
            $table->string('top_game_name')->nullable();
            $table->string('top_game_provider')->nullable();
            $table->string('top_game_system')->nullable();
            $table->string('top_provider')->nullable();
            $table->string('top_provider_system')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_io_users', function (Blueprint $table) {
            //
            $table->dropColumn('top_game_id');
            $table->dropColumn('top_game_name');
            $table->dropColumn('top_game_provider');
            $table->dropColumn('top_game_system');
            $table->dropColumn('top_provider');
            $table->dropColumn('top_provider_system');
        });
    }
};
