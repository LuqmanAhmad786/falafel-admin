<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloverCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clover_categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->unsignedInteger('restaurant_id');
            $table->string('category_name');
            $table->bigInteger('order_no')->default(0);
            $table->integer('reference_category_id')->default(0);
            $table->string('clover_id')->nullable();
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
        Schema::dropIfExists('clover_categories');
    }
}
