<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSavedCardV5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_cards', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('issue_number');
            $table->dropColumn('start_month');
            $table->dropColumn('start_year');
            $table->dropColumn('card_scheme_type');
            $table->dropColumn('card_scheme_name');
            $table->dropColumn('card_issuer');
            $table->dropColumn('country_code');
            $table->dropColumn('card_class');
            $table->dropColumn('card_product_type_desc_non_contactless');
            $table->dropColumn('card_product_type_desc_contactless');
            $table->dropColumn('prepaid');
            $table->dropColumn('restaurant_id');
            $table->dropColumn('card_group_id');
            $table->string('nickname')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('saved_cards', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('issue_number')->nullable();
            $table->string('start_month')->nullable();
            $table->string('start_year')->nullable();
            $table->string('card_scheme_type')->nullable();
            $table->string('card_scheme_name')->nullable();
            $table->string('card_issuer')->nullable();
            $table->string('country_code')->nullable();
            $table->string('card_class')->nullable();
            $table->string('card_product_type_desc_non_contactless')->nullable();
            $table->string('card_product_type_desc_contactless')->nullable();
            $table->string('prepaid')->nullable();
            $table->string('restaurant_id')->nullable();
            $table->string('card_group_id')->nullable();
        });
    }
}
