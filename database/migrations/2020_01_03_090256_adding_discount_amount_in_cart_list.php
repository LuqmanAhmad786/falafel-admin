<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddingDiscountAmountInCartList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_lists', function (Blueprint $table) {
            $table->decimal('discount_amount')->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
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
            $table->dropColumn('discount_amount');
            $table->dropColumn('coupon_id');
        });
    }
}
