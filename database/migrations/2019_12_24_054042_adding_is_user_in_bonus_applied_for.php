<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingIsUserInBonusAppliedFor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_applied_for', function (Blueprint $table) {
            $table->integer('is_used')->default(0)->comment('0 for not used & 1 for used')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_applied_for', function (Blueprint $table) {
            $table->dropColumn('is_used');
        });
    }
}
