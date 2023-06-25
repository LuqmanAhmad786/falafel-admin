<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMenuTableTiming extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menus',function (Blueprint $table){
            $table->time('from_2')->after('to');
            $table->time('to_2')->after('from_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menus',function (Blueprint $table){
            $table->dropColumn('from_2');
            $table->dropColumn('to_2');
        });
    }
}
