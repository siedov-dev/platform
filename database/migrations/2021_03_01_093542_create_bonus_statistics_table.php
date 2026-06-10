<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bonus_id');
            $table->integer('bonus_type');
            $table->integer('user_id');
            $table->integer('action');
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('currency');
            $table->integer('status_before')->default(0);
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('bonus_statistics');
    }
}
