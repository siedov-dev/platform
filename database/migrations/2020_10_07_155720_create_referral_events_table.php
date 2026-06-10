<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferralEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ref_id')->nullable(false);
            $table->integer('type');
            $table->bigInteger('user_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->float('profit')->default(0);
            $table->integer('currency')->nullable();
            $table->string('data')->nullable();
            $table->timestamps();

            $table->index('ref_id');
            $table->index('user_id');

            $table->foreign('ref_id', 'foreign_referral_id')->references('id')->on('user_referrals')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_events');
    }
}
