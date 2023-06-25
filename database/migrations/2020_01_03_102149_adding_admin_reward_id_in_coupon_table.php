<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingAdminRewardIdInCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_reward_id')->nullable();
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
            $table->dropColumn('admin_reward_id');
        });
    }
}
