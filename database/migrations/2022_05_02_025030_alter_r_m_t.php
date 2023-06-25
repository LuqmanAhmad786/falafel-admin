<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRMT extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurant_menu_timings', function (Blueprint $table) {
            $table->tinyInteger('offline')->default(0)->after('to_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurant_menu_timings', function (Blueprint $table) {
            $table->dropColumn('to_2');
        });
    }
}
