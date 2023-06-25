<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantMenuTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_menu_timings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('restaurant_menu_id');
            $table->string('day');
            $table->time('from_1')->nullable();
            $table->time('to_1')->nullable();
            $table->time('from_2')->nullable();
            $table->time('to_2')->nullable();
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
        Schema::dropIfExists('restaurant_menu_timings');
    }
}
