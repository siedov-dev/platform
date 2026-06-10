<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTvBetPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tv_bet_promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('amounts');
            $table->integer('total_count')->default(-1);
            $table->integer('activations')->default(1);
            $table->dateTime('expired_in');
            $table->mediumText('assign');
            $table->timestamps();

            $table->index('name');
        });

        Schema::table('user_use_promocode', function (Blueprint $table) {
            $table->integer('cnt')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tv_bet_promocodes');

        Schema::table('user_use_promocode', function (Blueprint $table) {
            $table->dropColumn('cnt');
        });
    }
}
