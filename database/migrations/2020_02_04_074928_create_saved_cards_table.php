<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavedCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('token');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('expiry_month');
            $table->string('expiry_year');
            $table->string('issue_number')->nullable();
            $table->string('start_month')->nullable();
            $table->string('start_year')->nullable();
            $table->string('card_type')->nullable();
            $table->string('masked_card_number');
            $table->string('card_scheme_type')->nullable();
            $table->string('card_scheme_name')->nullable();
            $table->string('card_issuer')->nullable();
            $table->string('country_code')->nullable();
            $table->string('card_class')->nullable();
            $table->string('card_product_type_desc_non_contact_less')->nullable();
            $table->string('card_product_type_desc_contact_less')->nullable();
            $table->string('prepaid')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saved_cards');
    }
}
