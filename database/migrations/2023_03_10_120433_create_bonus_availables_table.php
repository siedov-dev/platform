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
        Schema::create('bonus_availables', function (Blueprint $table) {
            $table->id();
            $table->integer('bonus_type');
            $table->integer('currency_id')->default(0);
            $table->text('params')->nullable();
            $table->mediumText('allowed_users')->nullable();
            $table->mediumText('users_activated')->nullable();
            $table->mediumText('games')->nullable();
            $table->dateTime('available_from');
            $table->dateTime('available_to');
            $table->timestamps();

            $table->index(['available_from', 'available_to']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_availables');
    }
};
