<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('promocode');
            $table->integer('bonus_type');
            $table->string('bonus_params');
            $table->integer('quantity');
            $table->dateTime('active_to');
            $table->integer('hall_id');
            $table->text('user_list')->nullable();
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
        Schema::dropIfExists('bonus_promocodes');
    }
}
