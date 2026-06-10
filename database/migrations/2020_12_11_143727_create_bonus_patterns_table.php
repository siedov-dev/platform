<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_patterns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->default('');
            $table->integer('description_page_id')->default(0);
            $table->integer('bonus_type');
            $table->text('bonus_params');
            $table->text('on_create_commands')->nullable();
            $table->text('on_activate_commands')->nullable();
            $table->text('on_done_commands')->nullable();
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
        Schema::dropIfExists('bonus_patterns');
    }
}
