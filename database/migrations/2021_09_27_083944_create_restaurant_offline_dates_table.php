<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantOfflineDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_offline_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('restaurant_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('offline_message');
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
        Schema::dropIfExists('restaurant_offline_dates');
    }
}
