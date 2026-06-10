<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_systems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type')->default(0)->comment('0 - payin, 1 - payout');
            $table->integer('currency_id');
            $table->integer('system_id');
            $table->string('system_name');
            $table->string('name');
            $table->string('method_alias');
            $table->string('image_url');
            $table->string('bank_alias')->nullable();
            $table->decimal('min_sum', 10, 2)->nullable();
            $table->decimal('max_sum', 10, 2)->nullable();
            $table->decimal('tax', 3, 2)->default(0);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        });

        Schema::table('payment_systems', function (Blueprint $table) {
            $table->index([
                'type',
                'currency_id',
                'system_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_systems');
    }
}
