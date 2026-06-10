<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefIdToMUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_users', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->index('ref_id');

            $table->foreign('ref_id', 'foreign_ref_id')->references('id')->on('user_referrals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_users', function (Blueprint $table) {
            //
            $table->dropForeign('foreign_ref_id');

            $table->dropColumn('ref_id');
        });
    }
}
