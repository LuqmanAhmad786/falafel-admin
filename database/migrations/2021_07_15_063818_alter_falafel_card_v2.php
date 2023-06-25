<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFalafelCardV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_falafel_cards', function (Blueprint $table) {
            $table->tinyInteger('non_deletable')->default(0);
            $table->tinyInteger('non_transferable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_falafel_cards', function (Blueprint $table) {
            $table->dropColumn('non_deletable');
//            $table->dropColumn('non_transferable');
        });
    }
}
