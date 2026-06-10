<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject');
            $table->integer('status')->default(0);
            $table->longText('emails');
            $table->text('html');
            $table->dateTime('send_at');
            $table->string('mail_system')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_schedules');
    }
}
