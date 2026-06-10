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
        Schema::create('user_online_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('users_count')->default(0);
            $table->mediumText('users');
            $table->string('status');
            $table->timestamp('dt');

            $table->index('dt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_online_logs');
    }
};
