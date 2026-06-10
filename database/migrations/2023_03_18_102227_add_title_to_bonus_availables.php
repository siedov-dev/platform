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
        Schema::table('bonus_availables', function (Blueprint $table) {
            //
            $table->string('title')->nullable();
            $table->string('img_url')->nullable();
            $table->string('descr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_availables', function (Blueprint $table) {
            //
            $table->dropColumn('descr');
            $table->dropColumn('img_url');
            $table->dropColumn('title');
        });
    }
};
