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
        Schema::table('user_referrals', function (Blueprint $table) {
            //
            $table->string('bonus_ref_code')->nullable();
            $table->index('bonus_ref_code');
            $table->index('ref_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            //
            $table->dropIndex('user_referrals_bonus_ref_code_index');
            $table->dropIndex('user_referrals_ref_code_index');
            $table->dropColumn('bonus_ref_code');
        });
    }
};
