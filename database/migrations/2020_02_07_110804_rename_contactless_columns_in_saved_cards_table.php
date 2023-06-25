<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameContactlessColumnsInSavedCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_cards', function (Blueprint $table) {
            $table->renameColumn('card_product_type_desc_non_contact_less', 'card_product_type_desc_non_contactless');
            $table->renameColumn('card_product_type_desc_contact_less', 'card_product_type_desc_contactless');
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
            //
        });
    }
}
