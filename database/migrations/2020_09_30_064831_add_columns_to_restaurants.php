<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRestaurants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->double('tax_rate', 10, 2)->nullable();
            $table->string('timezone')->nullable();
            $table->string('clover_mid')->nullable();
            $table->string('clover_api_key')->nullable();
            $table->string('clover_payment_api_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
            $table->dropColumn('timezone');
            $table->dropColumn('clover_mid');
            $table->dropColumn('clover_api_key');
            $table->dropColumn('clover_payment_api_key');

        });
    }
}
