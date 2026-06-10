<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type');
            $table->integer('koef');
            $table->integer('min_sum');
            $table->integer('max_sum');
            $table->integer('bonus_lifetime');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('send_sms')->default(0);
            $table->integer('send_email')->default(0);
            $table->integer('send_push')->default(0);
            $table->text('message_text')->nullable();
            $table->dateTime('send_time');
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
        Schema::dropIfExists('bonus_events');
    }
}
