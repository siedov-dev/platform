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
        Schema::create('slc_chest_items', function (Blueprint $table) {
            $table->id();
            $table->string('chest_type');
            $table->integer('group')->default(0);
            $table->decimal('chance', 6, 2)->default(0);
            $table->integer('item_type');
            $table->integer('amount_from')->default(0);
            $table->integer('amount_to')->default(0);
            $table->text('params')->nullable();
            $table->integer('bonus_type')->nullable();
            $table->text('bonus_params')->nullable();
            $table->text('bonus_games')->nullable();
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
        Schema::dropIfExists('slc_chest_items');
    }
};
