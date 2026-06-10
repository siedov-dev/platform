<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusMenuParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_menu_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('currency');
            $table->integer('bonus_type');
            $table->integer('bonus_pattern')->nullable();
            $table->string('name')->nullable();
            $table->decimal('min_value', 16, 4)->nullable();
            $table->decimal('max_value', 16, 4)->nullable();
            $table->decimal('value', 16, 4)->nullable();
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
        Schema::dropIfExists('bonus_menu_params');
    }
}
