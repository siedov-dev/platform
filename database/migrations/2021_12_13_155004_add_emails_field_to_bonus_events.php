<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailsFieldToBonusEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->mediumText('emails')->nullable();
            $table->mediumText('send_only_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_events', function (Blueprint $table) {
            //
            $table->dropColumn('emails');
            $table->dropColumn('send_only_to');
        });
    }
}
