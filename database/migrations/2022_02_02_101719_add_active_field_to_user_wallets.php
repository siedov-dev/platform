<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveFieldToUserWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            //
            $table->boolean('active')->default(false)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            //
            $table->dropColumn('active');
        });
    }
}

/*
 * activate current wallets
update user_wallets
set active = 1
where id in(select user_wallets.id
			from user_wallets
			inner join m_users on (user_wallets.user_id = m_users.id and m_users.currency = user_wallets.currency)
           );
update user_wallets
set active = 1
where amount > 0;
 */
