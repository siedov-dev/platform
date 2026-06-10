<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneMailFieldsToTelegramUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            //
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_confirm_code')->nullable();
            $table->string('email_confirm_code')->nullable();
            $table->dateTime('last_send_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            //
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('phone_confirm_code');
            $table->dropColumn('email_confirm_code');
            $table->dropColumn('last_send_at');
        });
    }
}
