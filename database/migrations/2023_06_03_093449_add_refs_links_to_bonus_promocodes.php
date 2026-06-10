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
            $table->bigInteger('affiliate_id')->nullable();
            $table->bigInteger('link_id')->nullable();
            $table->bigInteger('manager_client_id')->nullable();

            $table->index('affiliate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_promocodes', function (Blueprint $table) {
            $table->dropIndex('bonus_promocodes_affiliate_id_index');
            //
            $table->dropColumn('manager_client_id');
            $table->dropColumn('link_id');
            $table->dropColumn('affiliate_id');
        });
    }
};
