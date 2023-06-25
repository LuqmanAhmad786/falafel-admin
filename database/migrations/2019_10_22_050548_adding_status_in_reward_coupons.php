<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingStatusInRewardCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_coupons', function (Blueprint $table) {
            $table->integer('status')->default(1)->comment('1 for not used, 2 for used');
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
            $table->dropColumn('status');
        });
    }
}
