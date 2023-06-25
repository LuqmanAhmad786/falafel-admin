<?php

use Illuminate\Database\Seeder;

class RestaurantBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $restaurants = \App\Models\Restaurant::all();
        if(sizeof($restaurants)){
            \App\Models\StripeBankAccount::truncate();
            foreach ($restaurants AS $res){
                $bankDetails = [
                    'restaurant_id' => $res->id,
                    'bank_account_id' => 'acct_1KoT5HR9a4Kr3djF',
                    'bank_name' => "",
                    'account_number' => '000123456789',
                    'routing_number' => '110000000'
                ];
                \App\Models\StripeBankAccount::create($bankDetails);
            }
        }
    }
}
