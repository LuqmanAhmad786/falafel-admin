<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingItemInCartLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_lists', function (Blueprint $table) {
            $table->double('total_tax', 10, 2)->default(0)->nullable();;
            $table->double('order_total', 10, 2);
            $table->double('total_amount', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_lists', function (Blueprint $table) {
            $table->dropColumn('total_tax');
            $table->dropColumn('order_total');
            $table->dropColumn('total_amount');
        });
    }
}
