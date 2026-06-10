<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusCurrencyConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_currency_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bonus_type')->comment('0 - default');
            $table->integer('currency');
            $table->integer('type')->default(0);
            $table->integer('round_numbers')->default(0);
            $table->float('spins_k')->default(1);
            $table->float('money_k')->default(1);
            $table->float('wager_k')->default(1);
            $table->float('max_bet_k')->default(1);
            $table->float('money_to_activate_k')->default(1);
            $table->float('min_deposit_k')->default(1);
            $table->float('max_deposit_k')->default(1);
            $table->float('min_win_sum_k')->default(1);
            $table->float('max_win_sum_k')->default(1);
            $table->float('min_win_coef_k')->default(1);
            $table->float('max_win_coef_k')->default(1);
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
        Schema::dropIfExists('bonus_currency_configs');
    }
}
;
