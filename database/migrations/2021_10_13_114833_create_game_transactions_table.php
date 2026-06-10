<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('system');
            $table->bigInteger('user_id');
            $table->bigInteger('bonus')->nullable();
            $table->string('action');
            $table->decimal('amount', 10, 3);
            $table->string('currency', 10);
            $table->string('game_id');
            $table->string('session_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('date');
            $table->boolean('refunded')->default(false);
            $table->string('bet_transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::table('game_transactions', function (Blueprint $table) {
            $table->index(['system']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_transactions');
    }
}
