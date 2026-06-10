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
        Schema::create('user_game_stats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('currency', 10);
            $table->string('session_id')->nullable();
            $table->string('system')->nullable();
            $table->string('game_id')->nullable();
            $table->decimal('login_balance', 16, 4)->default(0);
            $table->decimal('logout_balance', 16, 4)->default(0);
            $table->decimal('balance_diff', 16, 4)->default(0);
            $table->decimal('full_total_bet', 16, 4)->default(0);
            $table->decimal('full_total_win', 16, 4)->default(0);
            $table->decimal('real_total_bet', 16, 4)->default(0);
            $table->decimal('real_total_win', 16, 4)->default(0);
            $table->decimal('total_bet', 16, 4)->default(0);
            $table->decimal('total_win', 16, 4)->default(0);
            $table->timestamp('login_date')->nullable();
            $table->timestamp('logout_date')->nullable();
            $table->timestamp('last_active_date')->nullable();
            $table->timestamps();
            $table->string('outer_table')->nullable();
            $table->bigInteger('outer_id')->nullable();
            $table->integer('status')->default(0);

            $table->index('user_id');
            $table->index(['system', 'game_id', 'currency']);
            $table->index('login_date', 'logout_date');
            $table->index('outer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_game_stats');
    }
};
