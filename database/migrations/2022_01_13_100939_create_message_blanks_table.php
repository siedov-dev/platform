<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageBlanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_blanks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('sms_text')->nullable();
            $table->mediumText('emails')->nullable();
            $table->text('tg_message')->nullable();
            $table->mediumText('tg_media_type')->nullable();
            $table->mediumText('tg_image')->nullable();
            $table->text('viber_message')->nullable();
            $table->mediumText('viber_media_type')->nullable();
            $table->mediumText('viber_image')->nullable();
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
        Schema::dropIfExists('message_blanks');
    }
}
