<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingTypeInUserRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_rewards', function (Blueprint $table) {
            $table->integer('type')->default(1)->comment('1 for credit 2 for debit 3 for birthday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_rewards', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
