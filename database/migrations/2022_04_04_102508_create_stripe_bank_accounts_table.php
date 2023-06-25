<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripeBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('restaurant_id');
            $table->string('bank_account_id');
            $table->string('bank_name')->nullable();
            $table->string('account_number');
            $table->string('routing_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_bank_accounts');
    }
}
