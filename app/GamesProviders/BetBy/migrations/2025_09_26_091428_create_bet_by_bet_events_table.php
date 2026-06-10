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
        Schema::create('bet_by_bet_events', function (Blueprint $table) {
            $table->id();
            $table->string("event_type");
            $table->bigInteger("bet_id")->unsigned();
            $table->bigInteger("transaction_id")->unsigned();

            $table->bigInteger("amount");
            $table->string("currency");
            $table->boolean("is_cashout")->default(false);
            $table->bigInteger("bet_transaction_id")->unsigned();
            $table->boolean("is_snr_lost")->default(false);
            $table->float("odds")->default(0);
            $table->bigInteger("bonus_id")->unsigned();
            $table->string("bonus_type");
            $table->float("comboboost_multiplier");
            $table->text("selections")->nullable();

            $table->bigInteger("parent_transaction_id")->unsigned()->nullable();

            $table->timestamps();
            $table->index("bet_id");
            $table->index("bet_transaction_id");
            $table->index("transaction_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bet_by_bet_events');
    }
};
