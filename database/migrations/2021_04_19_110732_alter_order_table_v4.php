<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrderTableV4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('order_type')->comment('1 pickup 2 delivery')->default(0);
            $table->string('delivery_address')->nullable();
            $table->string('delivery_notes')->nullable();
            $table->string('postmates_delivery_id')->nullable();
            $table->string('postmates_tracking_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
            $table->dropColumn('delivery_address');
            $table->dropColumn('delivery_notes');
            $table->dropColumn('postmates_delivery_id');
            $table->dropColumn('postmates_tracking_url');
        });
    }
}
