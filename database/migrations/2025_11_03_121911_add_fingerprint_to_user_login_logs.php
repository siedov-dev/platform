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
        Schema::table('user_login_logs', function (Blueprint $table) {
            //
            $table->string('fingerprint')->nullable();

            $table->index('fingerprint', 'fingerprint_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            //
            $table->dropIndex('fingerprint_idx');
            $table->dropColumn('fingerprint');
        });
    }
};
