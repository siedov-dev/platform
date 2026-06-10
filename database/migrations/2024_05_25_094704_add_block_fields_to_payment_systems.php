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
        Schema::table('payment_systems', function (Blueprint $table) {
            //
            $table->boolean('block_multideps')->default(false);
            $table->integer('block_after_deps')->default(5);
            $table->integer('block_minutes')->default(5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_systems', function (Blueprint $table) {
            //
            $table->dropColumn('block_minutes');
            $table->dropColumn('block_after_deps');
            $table->dropColumn('block_multideps');
        });
    }
};
