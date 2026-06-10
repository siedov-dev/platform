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
        Schema::create('user_task_params', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id');
            $table->string('currency', 10)->nullable();
            $table->string('type');
            $table->integer('target_value')->default(0);
            $table->decimal('float_param', 16, 4)->default(0);
            $table->integer('int_param')->default(0);
            $table->string('string_param')->default('');
            $table->timestamps();

            $table->index(['group_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_task_params');
    }
};
