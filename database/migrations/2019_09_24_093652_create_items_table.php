<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->string('item_name');
            $table->double('item_price', 10, 2);
            $table->integer('tax_rate')->nullable();
            $table->string('item_image')->nullable();
            $table->string('item_thumbnail')->nullable();
            $table->string('item_description', 500)->nullable();
            $table->tinyInteger('its_own')->nullable();
            $table->unsignedInteger('complete_meal_of')->nullable();
            $table->unsignedInteger('restaurant_id');
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
        Schema::dropIfExists('items');
    }
}
