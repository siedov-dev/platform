<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLangImgUrlsFieldToMessageBlanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_blanks', function (Blueprint $table) {
            //
            $table->text('lang_img_urls')->after('tg_image')->nullable();
            $table->text('lang_messages')->after('tg_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_blanks', function (Blueprint $table) {
            //
            $table->dropColumn('lang_img_urls');
            $table->dropColumn('lang_messages');
        });
    }
}
