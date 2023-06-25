<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingCouponTypeInRewardCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_coupons', function (Blueprint $table) {
            $table->integer('coupon_type')->comment('1 for basic, 2 for birthday, 3 for admin reward')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reward_coupons', function (Blueprint $table) {
            $table->dropColumn('coupon_type');
        });
    }
}
