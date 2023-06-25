<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_rewards', function (Blueprint $table) {
            $table->bigIncrements('admin_reward_id');
            $table->string('name');
            $table->string('description');
            $table->integer('expiry');
            $table->integer('item_id')->nullable();
            $table->integer('reward_point')->nullable();
            $table->integer('unique_key');
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
        Schema::dropIfExists('admin_rewards');
    }
}
