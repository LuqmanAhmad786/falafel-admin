<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloverItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clover_items', function (Blueprint $table) {
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
            $table->bigInteger('order_no')->default(0);
            $table->string('item_image_single')->nullable();
            $table->integer('reference_item_id')->default(0);
            $table->tinyInteger('is_common')->default(2)->comment('1 yes 2 no');
            $table->tinyInteger('is_in_stock')->default(1)->comment('1 in stock 0 out of stock');
            $table->string('clover_id')->nullable();
            $table->integer('clover_stock')->default(0);
            $table->tinyInteger('menu_type')->default(1)->comment('1 menu 2 modifier');
            $table->tinyInteger('tax_applicable')->comment('1 Yes 0 No')->nullable();
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
        Schema::dropIfExists('clover_items');
    }
}
