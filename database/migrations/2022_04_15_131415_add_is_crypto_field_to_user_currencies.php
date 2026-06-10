<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCryptoFieldToUserCurrencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_currencies', function (Blueprint $table) {
            //
            $table->integer('denomination')->default(1)->after('code');
            $table->boolean('is_crypto')->default(false)->after('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_currencies', function (Blueprint $table) {
            //
            $table->dropColumn('denomination');
            $table->dropColumn('is_crypto');
        });
    }
}
