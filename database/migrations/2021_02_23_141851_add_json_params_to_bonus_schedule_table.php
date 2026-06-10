<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJsonParamsToBonusScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            if (!Schema::hasColumn('bonus_schedule', 'json_params')) {
                $table->string('json_params')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_schedule', function (Blueprint $table) {
            //
            $table->dropColumn('json_params');
        });
    }
}
