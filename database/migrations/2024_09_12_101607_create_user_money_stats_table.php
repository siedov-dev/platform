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
        Schema::create('user_money_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('currency_id');
            $table->string('type');
            $table->integer('deposit_count')->default(0);
            $table->decimal('deposit_sum', 16, 4)->default(0);
            $table->dateTime('first_dep')->nullable();
            $table->dateTime('last_dep')->nullable();
            $table->integer('payout_count')->default(0);
            $table->decimal('payout_sum', 16, 4)->default(0);
            $table->dateTime('first_payout')->nullable();
            $table->dateTime('last_payout')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'currency_id', 'type']);
            $table->foreign('user_id')->references('id')->on('m_users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_money_stats');
    }
};
