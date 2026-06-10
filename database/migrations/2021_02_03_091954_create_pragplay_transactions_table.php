<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePragplayTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pragplay_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->integer('bonus')->default(0);
            $table->decimal('amount', 10, 4)->default(0);
            $table->string('action');
            $table->string('currency');
            $table->string('game_id');
            $table->string('session_id');
            $table->string('transaction_id');
            $table->string('type')->nullable();
            $table->boolean('refunded')->default(false);
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
        Schema::dropIfExists('pragplay_transactions');
    }
}
