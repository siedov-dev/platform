<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToMailConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_configs', function (Blueprint $table) {
            //
            $table->string('from_email')->nullable();
            $table->string('unisender_key')->nullable();
            $table->string('sendgrid_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_configs', function (Blueprint $table) {
            //
            $table->dropColumn('from_email');
            $table->dropColumn('unisender_key');
            $table->dropColumn('sendgrid_key');
        });
    }
}
