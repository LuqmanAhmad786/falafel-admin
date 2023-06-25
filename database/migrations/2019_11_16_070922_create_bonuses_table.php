<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus', function (Blueprint $table) {
            $table->bigIncrements('bonus_id');
            $table->integer('bonus_type');
            $table->integer('bonus_condition_type');
            $table->string('bonus_name');
            $table->string('bonus_expiry')->nullable();
            $table->string('notification_text')->nullable();
            $table->string('description')->nullable();
            $table->string('term_and_condition')->nullable();
            $table->string('bonus_free_item_id')->nullable();
            $table->string('bonus_extra_point')->nullable();
            $table->string('bonus_points_multiplier')->nullable();
            $table->string('bonus_discount')->nullable();
            $table->string('bonus_orders_no')->nullable();
            $table->string('bonus_start_date')->nullable();
            $table->string('bonus_end_date')->nullable();
            $table->string('bonus_start_hour')->nullable();
            $table->string('bonus_end_hour')->nullable();
            $table->string('bonus_plates_no')->nullable();
            $table->string('bonus_user_points')->nullable();
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
        Schema::dropIfExists('bonuses');
    }
}
