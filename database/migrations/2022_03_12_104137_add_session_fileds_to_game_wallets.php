<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSessionFiledsToGameWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slerg_wallets', function (Blueprint $table) {
            $table->string('session_id')->nullable();
        });

        Schema::table('prag_wallets', function (Blueprint $table) {
            $table->string('session_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slerg_wallets', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });

        Schema::table('prag_wallets', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });
    }
}
