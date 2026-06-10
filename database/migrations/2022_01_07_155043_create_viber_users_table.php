<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViberUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viber_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('viber_id');
            $table->bigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->string('language', 10)->nullable();
            $table->string('country')->nullable();
            $table->string('api_ver')->nullable();
            $table->integer('status')->default(0);
            $table->string('data')->nullable();
            $table->integer('req_status')->default(0);
            $table->integer('req_message_id')->default(0);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_confirm_code')->nullable();
            $table->string('email_confirm_code')->nullable();
            $table->dateTime('last_send_at')->nullable();
            $table->timestamps();
        });

        Schema::table('viber_users', function (Blueprint $table) {
            $table->index('viber_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viber_users');
    }
}
