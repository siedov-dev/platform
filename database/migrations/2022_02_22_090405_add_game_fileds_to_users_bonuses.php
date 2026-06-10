<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGameFiledsToUsersBonuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_bonuses', function (Blueprint $table) {
            $table->string('game_provider')->nullable();
            $table->string('game_id')->nullable();
        });

        Schema::table('users_bonuses', function (Blueprint $table) {
            $table->index(['game_id','game_provider']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_bonuses', function (Blueprint $table) {
            $table->dropIndex(['game_id','game_provider']);
        });

        Schema::table('users_bonuses', function (Blueprint $table) {
            $table->dropColumn('game_provider');
            $table->dropColumn('game_id');
        });
    }
}
