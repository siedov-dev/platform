<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('m_users', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE `m_users` RENAME COLUMN `country_krak` TO `last_tz`');
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
        });
        DB::statement('ALTER TABLE `m_users` RENAME COLUMN `last_tz` TO `country_krak`');
    }
};
