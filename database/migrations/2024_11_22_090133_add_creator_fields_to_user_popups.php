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
        Schema::table('user_popups', function (Blueprint $table) {
            //
            $table->bigInteger('creator_id')->default(0);
            $table->integer('creator_type')->default(0);

            $table->index(['creator_id', 'creator_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_popups', function (Blueprint $table) {
            //
            $table->dropIndex('user_popups_creator_id_creator_type_index');

            $table->dropColumn('creator_type');
            $table->dropColumn('creator_id');
        });
    }
};
