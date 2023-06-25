<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingAddedByInFavoriteLabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorite_labels', function (Blueprint $table) {
            $table->integer('added_by')->default(1)->comment('1 if added by admin & 2 if added by user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('favorite_labels', function (Blueprint $table) {
            $table->dropColumn('added_by');
        });
    }
}
