<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingItemInfoInModifierItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modifier_items', function (Blueprint $table) {
            $table->string('item_name')->nullable();
            $table->decimal('item_price',10,2)->nullable();
            $table->string('item_image')->nullable();
            $table->string('item_description')->nullable();
            $table->tinyInteger('its_own')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modifier_items', function (Blueprint $table) {
           $table->dropColumn('item_name');
           $table->dropColumn('item_price');
           $table->dropColumn('item_image');
           $table->dropColumn('item_description');
           $table->dropColumn('its_own');
        });
    }
}
