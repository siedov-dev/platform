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
        Schema::create('user_popup_schedules', function (Blueprint $table) {
            $table->id();
            $table->longText('user_ids')->nullable();
            $table->string('category');
            $table->string('img');
            $table->mediumText('locale_texts');
            $table->dateTime('send_at');
            $table->dateTime('available_to');
            $table->boolean('send_mail')->default(false);
            $table->string('button_type');
            $table->string('button_url');
            $table->boolean('show_no_more')->default(false);
            $table->integer('bonus_type')->nullable();
            $table->mediumText('bonus_params')->nullable();
            $table->mediumText('bonus_games')->nullable();
            $table->string('bonus_title')->nullable();
            $table->string('bonus_img')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->index('send_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_popup_schedules');
    }
};
