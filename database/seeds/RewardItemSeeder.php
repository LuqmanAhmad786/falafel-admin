<?php

use Illuminate\Database\Seeder;
use App\Models\RewardsItem;
class RewardItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'item_id' => 679,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 25,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 680,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 30,
                'is_for_gold_only' => 1,
            ],
            [
                'item_id' => 681,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 50,
                'is_for_gold_only' => 1,
            ],
            [
                'item_id' => 682,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 50,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 683,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 100,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 684,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 200,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 685,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 300,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 686,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 400,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 687,
                'restaurant_id' => 1,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 500,
                'is_for_gold_only' => 0,
            ],
        ];
        App\Models\RewardsItem::insert($data);
    }
}
