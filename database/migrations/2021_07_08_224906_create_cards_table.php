<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('card_category_id');
            $table->string('card_name');
            $table->string('card_image');
            $table->decimal('card_amount');
            $table->tinyInteger('card_type')->comment('1 falafel card 2 giftcard 3 both');
            $table->tinyInteger('is_featured')->comment('1 yes 0 no');
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
        Schema::dropIfExists('cards');
    }
}
