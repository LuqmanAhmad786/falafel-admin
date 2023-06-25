<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifierGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->increments('modifier_group_id');
            $table->string('modifier_group_name');
            $table->tinyInteger('item_exactly')->nullable();
            $table->tinyInteger('item_range_from')->nullable();
            $table->tinyInteger('item_range_to')->nullable();
            $table->tinyInteger('item_maximum')->nullable();
            $table->tinyInteger('single_item_maximum')->nullable();
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
        Schema::dropIfExists('modifier_groups');
    }
}
