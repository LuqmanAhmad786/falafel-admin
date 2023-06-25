<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRewardItemsTabls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rewards_items', function (Blueprint $table) {
            $table->mediumInteger('points_required')->default(0);
            $table->tinyInteger('is_for_gold_only')->default(0);
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
            //
        });
    }
}
