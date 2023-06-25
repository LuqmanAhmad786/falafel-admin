<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new \App\User();
        $user->first_name = 'Server';
        $user->last_name = 'User';
        $user->email = 'server1@fcorner.us';
        $user->mobile = '00000000';
        $user->password = Hash::make('Hi@fc');
        $user->customer_id = 'cus_' . generateCustomerId();
        $user->is_server_user = 1;
        $user->assigned_restaurant = 1;
        $user->save();
    }
}
