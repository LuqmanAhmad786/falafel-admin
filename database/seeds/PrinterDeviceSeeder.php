<?php

use Illuminate\Database\Seeder;
use App\Models\PrinterDevice;

class PrinterDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PrinterDevice::truncate();

        PrinterDevice::insert([
           'device_mac_address' => '00:11:62:0d:79:67',
            'queue_id' => 1,
            'dot_width' => 576,
            'status' => '200 OK',
            'device_type' => 'Star Intelligent Interface HE01x/HE02x',
            'device_version' => '1.9.0',
            'is_printing' => 0,
            'last_poll' => time()
        ]);
    }
}
