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
        Schema::table('user_ranks', function (Blueprint $table) {
            //
            $table->integer('currency_id')->default(1)->after('id');

            $table->index(['currency_id', 'rank_level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_ranks', function (Blueprint $table) {
            //
            $table->dropIndex('user_ranks_currency_id_rank_level_index');
            $table->dropColumn('currency_id');
        });
    }
};
