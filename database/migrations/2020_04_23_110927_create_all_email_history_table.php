<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllEmailHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('all_email_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('total')->default(0);
            $table->integer('sent')->default(0);
            $table->integer('not_sent')->default(0);
            $table->string('title')->default('');
            $table->text('text');
            $table->text('html');
            $table->string('sender')->default('');
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
        Schema::dropIfExists('all_email_history');
    }
}
