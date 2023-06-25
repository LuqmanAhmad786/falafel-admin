<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('order_id');
            $table->string('order_code');
            $table->string('token');
            $table->string('order_description');
            $table->string('amount');
            $table->string('currency_code');
            $table->string('payment_status');

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
            $table->string('card_product_type_desc_non_contactless')->nullable();
            $table->string('card_product_type_desc_contactless')->nullable();
            $table->string('prepaid')->nullable();

            $table->string('is3_ds_order')->nullable();
            $table->string('authorize_only')->nullable();
            $table->string('customer_order_code')->nullable();
            $table->string('environment')->nullable();

            $table->string('avs_result_code')->nullable();
            $table->string('cvc_result_code')->nullable();

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
        Schema::dropIfExists('order_payments');
    }
}
