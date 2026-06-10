<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVegasFreespinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vegas_freespins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->integer('currency');
            $table->string('bonus_id');
            $table->integer('status')->default(0);
            $table->integer('spins')->default(0);
            $table->integer('amount')->default(0);
            $table->decimal('activation_sum', 8, 2)->default(0);
            $table->integer('hours_active')->default(48);
            $table->mediumText('games')->nullable();
            $table->dateTime('active_to');
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
        Schema::dropIfExists('vegas_freespins');
    }
}
