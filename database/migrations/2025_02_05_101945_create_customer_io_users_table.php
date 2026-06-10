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
        Schema::create('customer_io_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->timestamp('reg_date');
            $table->date('birthday')->nullable();
            $table->timestamp('first_dep_date')->nullable();
            $table->timestamp('last_dep_date')->nullable();
            $table->string('email')->default("");
            $table->boolean('email_verified')->default(false);
            $table->string('first_name')->default("");
            $table->string('last_name')->default("");
            $table->string('phone')->default("");
            $table->boolean('phone_verified')->default(false);
            $table->integer('rank')->default(0);
            $table->string('partner')->default("");
            $table->integer('currency');
            $table->string('lang', 10)->default("");
            $table->string('country')->default("");
            $table->decimal('dep_sum', 16,2)->default(0);
            $table->decimal('dep_avg', 16,2)->default(0);
            $table->integer('dep_cnt')->default(0);
            $table->boolean('blocked')->default(false);
            $table->decimal('dep_sum_7d', 16,2)->default(0);
            $table->decimal('dep_sum_30d', 16,2)->default(0);
            $table->integer('b1')->default(0);
            $table->integer('b1fs')->default(0);
            $table->integer('b2')->default(0);
            $table->integer('b2fs')->default(0);
            $table->integer('b3')->default(0);
            $table->integer('b3fs')->default(0);
            $table->integer('b4')->default(0);
            $table->integer('b5')->default(0);
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_io_users');
    }
};
