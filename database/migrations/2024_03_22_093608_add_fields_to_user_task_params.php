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
        Schema::table('user_task_params', function (Blueprint $table) {
            //
            $table->integer('target_value_to')->default(0)->after('target_value');
            $table->integer('target_value_step')->default(1)->after('target_value_to');
            $table->decimal('float_param_to', 16, 4)->default(0)->after('float_param');
            $table->decimal('float_param_step', 16, 4)->default(1)->after('float_param_to');
            $table->integer('int_param_to')->default(0)->after('int_param');
            $table->integer('int_param_step')->default(1)->after('int_param_to');
            $table->mediumText('text_param')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_task_params', function (Blueprint $table) {
            //
            $table->dropColumn('text_param');
            $table->dropColumn('int_param_step');
            $table->dropColumn('int_param_to');
            $table->dropColumn('float_param_step');
            $table->dropColumn('float_param_to');
            $table->dropColumn('target_value_step');
            $table->dropColumn('target_value_to');
        });
    }
};
