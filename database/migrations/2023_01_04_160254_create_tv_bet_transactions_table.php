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
        Schema::create('tv_bet_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bid');
            $table->integer('tt');
            $table->bigInteger('user_id')->nullable();
            $table->string('si');
            $table->string('to');
            $table->decimal('sm', 16, 4)->nullable();
            $table->string('cc', 10)->nullable();
            $table->bigInteger('ti');
            $table->string('pr')->nullable();
            $table->string('ed')->nullable();
            $table->timestamps();

            $table->index(['bid', 'tt']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tv_bet_transactions');
    }
};
