<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * update `disabled_games` set device ='desktop';
     * @return void
     */
    public function up()
    {
        Schema::table('disabled_games', function (Blueprint $table) {
            //
            $table->string('device')->nullable()->after('id');

            $table->index(['device', 'type', 'cid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disabled_games', function (Blueprint $table) {
            //
            $table->dropIndex('disabled_games_device_type_cid_index');
            $table->dropColumn('device');
        });
    }
};
