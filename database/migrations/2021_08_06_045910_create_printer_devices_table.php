<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrinterDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printer_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_mac_address');
            $table->string('queue_id');
            $table->string('dot_width');
            $table->string('status');
            $table->string('device_type');
            $table->string('device_version');
            $table->string('is_printing');
            $table->string('last_poll');
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
        Schema::dropIfExists('printer_devices');
    }
}
