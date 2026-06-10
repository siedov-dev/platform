<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViberPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viber_promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status')->default(0);
            $table->integer('type')->default(0);
            $table->string('promocode')->nullable();
            $table->integer('bonus_type');
            $table->text('bonus_params')->nullable();
            $table->dateTime('active_to');
            $table->dateTime('send_at');
            $table->text('message');
            $table->mediumText('user_list')->nullable();
            $table->mediumText('user_received')->nullable();
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
        Schema::dropIfExists('viber_promocodes');
    }
}
