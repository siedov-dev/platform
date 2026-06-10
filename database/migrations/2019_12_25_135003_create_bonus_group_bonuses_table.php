<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateBonusGroupBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_group_bonuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->integer('index');
            $table->integer('bonus_type');
            $table->text('bonus_params');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('send_time');
            $table->text('message_text');
            $table->integer('send_sms');
            $table->integer('send_email');
            $table->integer('send_push');
            $table->integer('days_to_activate');
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
        Schema::dropIfExists('bonus_group_bonuses');
    }
}
