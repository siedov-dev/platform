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
        Schema::create('slc_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('level')->default(0);
            $table->integer('currency')->default(0);
            $table->integer('target_value')->default(0);
            $table->decimal('float_param', 16, 4)->default(0);
            $table->integer('int_param')->default(0);
            $table->string('string_param')->default(0);
            $table->integer('slc_reward')->default(0);
            $table->integer('bonus_type')->default(0);
            $table->text('bonus_params')->nullable();
            $table->text('bonus_games')->nullable();
            $table->boolean('is_disabled')->default(false);
            $table->timestamps();

            $table->index(['type', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_achievements');
    }
};
