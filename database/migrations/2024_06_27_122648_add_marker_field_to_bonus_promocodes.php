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
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->integer('marker')->default(0);
            $table->index('marker');
        });

        Schema::table('users_bonuses', function (Blueprint $table) {
            //
            $table->integer('marker')->default(0);
            $table->index('marker');
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
            //
            $table->dropIndex('users_bonuses_marker_index');
            $table->dropColumn('marker');
        });

        Schema::table('bonus_promocodes', function (Blueprint $table) {
            //
            $table->dropIndex('bonus_promocodes_marker_index');
            $table->dropColumn('marker');
        });
    }
};
