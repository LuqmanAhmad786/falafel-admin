<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifierItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifier_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('modifier_group_id');
            $table->unsignedInteger('item_id');
            $table->tinyInteger('added_from')->comment('1 from item 2 from modifier');
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
        Schema::dropIfExists('modifier_items');
    }
}
