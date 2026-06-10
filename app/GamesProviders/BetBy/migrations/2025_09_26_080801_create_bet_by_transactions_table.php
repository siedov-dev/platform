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
        Schema::create('bet_by_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("betslip_id")->unsigned();
            $table->string("player_id");
            $table->string("operator_id");
            $table->string("operator_brand_id");
            $table->bigInteger("ext_player_id")->unsigned();
            $table->decimal('timestamp', 16, 6);
            $table->bigInteger("amount");
            $table->string("currency", 10);
            $table->decimal("cross_rate_euro", 12, 6);
            $table->string("operation");
            $table->bigInteger("bonus_id")->unsigned();
            $table->bigInteger("parent_transaction_id")->unsigned();
            $table->timestamps();

            $table->index("player_id");
            $table->index("ext_player_id");
            $table->index("parent_transaction_id");
            $table->index(['id', 'operation', 'ext_player_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bet_by_transactions');
    }
};
