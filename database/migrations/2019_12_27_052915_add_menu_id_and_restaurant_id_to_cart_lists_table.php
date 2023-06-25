<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenuIdAndRestaurantIdToCartListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_lists', function (Blueprint $table) {
            $table->unsignedInteger('menu_id')->nullable();
            $table->unsignedInteger('restaurant_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_lists', function (Blueprint $table) {
            $table->dropColumn('menu_id');
            $table->dropColumn('restaurant_id');
        });
    }
}
