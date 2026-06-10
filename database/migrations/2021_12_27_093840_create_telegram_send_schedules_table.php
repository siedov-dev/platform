<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelegramSendSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_send_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status')->default(0);
            $table->dateTime('send_at');
            $table->text('text')->nullable();
            $table->string('media_type')->nullable();
            $table->mediumText('file_data');
            $table->text('to_channels');
            $table->mediumText('to_users');
            $table->timestamps();
        });

        Schema::table('telegram_send_schedules', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_send_schedules');
    }
}
