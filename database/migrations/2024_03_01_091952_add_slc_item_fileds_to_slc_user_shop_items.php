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
        Schema::table('slc_user_shop_items', function (Blueprint $table) {
            //
            $table->integer('type');
            $table->integer('bonus_type')->nullable();
            $table->text('params')->nullable();
            $table->text('allowed_games')->nullable();
            $table->dropColumn('shop_item_id');
            $table->string('item_source')->nullable();
            $table->bigInteger('source_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slc_user_shop_items', function (Blueprint $table) {
            //
            $table->bigInteger('shop_item_id')->nullable();
            $table->dropColumn('type');
            $table->dropColumn('bonus_type');
            $table->dropColumn('params');
            $table->dropColumn('allowed_games');
            $table->dropColumn('item_source');
            $table->dropColumn('source_id');
        });
    }
};
