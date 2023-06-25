<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingFlagInRewardItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rewards_items', function (Blueprint $table) {
            $table->integer('flag')->default(1)->comment('1 for reward point 2 for birthday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rewards_items', function (Blueprint $table) {
            $table->dropColumn('flag');
        });
    }
}
