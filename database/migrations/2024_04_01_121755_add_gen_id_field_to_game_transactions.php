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
            $table->string('gen_id')->nullable()->after('id');
            $table->index('gen_id');
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
            $table->dropIndex('game_transactions_gen_id_index');
            $table->dropColumn('gen_id');
        });
    }
};
