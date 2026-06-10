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
        Schema::create('slc_slot_machine_symbols', function (Blueprint $table) {
            $table->id();
            $table->integer('drum')->default(0);
            $table->string('type');
            $table->timestamps();

            $table->index('drum');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_slot_machine_symbols');
    }
};
