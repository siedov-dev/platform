<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_currencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 6);
            $table->integer('hall_id');
            $table->string('symbol', 10);
            $table->integer('code');
            $table->integer('sport_code')->default(0);
            $table->string('vegas_bank');
            $table->string('vegas_bonus_bank');
            $table->decimal('min_payin_sum', 10, 2);
            $table->decimal('min_payout_sum', 10, 2);
            $table->decimal('max_auto_payout', 10, 2);
            $table->decimal('max_payout_sum', 10, 2);
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
        Schema::dropIfExists('user_currencies');
    }
}
