<?php

use Illuminate\Database\Seeder;
use App\Models\Restaurant\RestaurantMenuTiming;
class RestaurantTimingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RestaurantMenuTiming::truncate();
        RestaurantMenuTiming::insert([
            [
                'restaurant_menu_id' => 48,
                'day' => 'monday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'tuesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'wednesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'thursday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'friday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'saturday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 48,
                'day' => 'sunday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ]
        ]);

        RestaurantMenuTiming::insert([
            [
                'restaurant_menu_id' => 49,
                'day' => 'monday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'tuesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'wednesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'thursday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'friday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'saturday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => 49,
                'day' => 'sunday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ]
        ]);
    }
}
