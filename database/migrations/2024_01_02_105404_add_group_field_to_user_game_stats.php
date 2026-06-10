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
        Schema::table('user_game_stats', function (Blueprint $table) {
            //
            $table->string('group')->nullable()->after('system');

            $table->index(['system', 'group', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_game_stats', function (Blueprint $table) {
            //
            $table->dropIndex('user_game_stats_system_group_currency_index');
            $table->dropColumn('group');
        });
    }
};
