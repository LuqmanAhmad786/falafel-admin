<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrderRefundQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refund_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('transaction_id');
            $table->decimal('amount', 18,2);
            $table->decimal('order_total', 18,2);
            $table->decimal('total_tax', 18,2);
            $table->decimal('discount_amount', 18,2);
            $table->tinyInteger('status')->comment('0 pending 1 completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_refund_queue');
    }
}
