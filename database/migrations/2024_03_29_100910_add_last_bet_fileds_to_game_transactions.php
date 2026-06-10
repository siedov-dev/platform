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
        Schema::table('game_transactions', function (Blueprint $table) {
            //
            $table->decimal('last_bet_value', 16, 4)->default(0);
            $table->string('last_bet_id', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('last_bet_id');
            $table->dropColumn('last_bet_value');
        });
    }
};
