<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->bigInteger('reference_id');
            $table->unsignedInteger('user_id');
            $table->integer('restaurant_id');
            $table->integer('pickup_time');
            $table->integer('preparation_time');
            $table->double('total_amount', 10, 2);
            $table->integer('status')->default(1)->comment('1 for confirmed, 2 taken/received, 3 completed');
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
        Schema::dropIfExists('orders');
    }
}
