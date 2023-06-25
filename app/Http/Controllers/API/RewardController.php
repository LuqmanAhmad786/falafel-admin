<?php

namespace App\Http\Controllers\API;

use App\Models\RewardsItem;
use App\Models\User\UserMembership;
use App\Models\User\UserRewardItems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function getRewardItems(){
        $items = RewardsItem::with(['item'])
            ->where('is_enable',1)
            ->groupBy('item_id')
            ->get();

        $lastRedeemedItem = 0;
        $redeemed = UserRewardItems::where('user_id',Auth::user()->id)
            ->orderBy('created_at','desc')->first();
        $membership = UserMembership::where('user_id',Auth::user()->id)->first();

        if($redeemed){
            if($redeemed->reward_item_id == 1 && $membership->membership_id != 3){
                $lastRedeemedItem = 3;
            }else{
                $lastRedeemedItem = $redeemed->reward_item_id;
            }
        }

        foreach ($items AS $item){
            if($lastRedeemedItem == 0){
                if($item->reward_item_id == 1){
                    $item->is_locked = 0;
                }else{
                    $item->is_locked = 1;
                }
            }else{
                if ($item->reward_item_id <= $lastRedeemedItem) {
                    $item->is_locked = 2;
                } elseif ($item->reward_item_id == $lastRedeemedItem + 1) {
                    $item->is_locked = 0;
                } else {
                    $item->is_locked = 1;
                }
            }

            if($membership->membership_id != 3){
                if($item->reward_item_id == 2 || $item->reward_item_id == 3){
                    $item->is_locked = 1;
                }
            }
        }
        return response()->json(apiResponseHandler($items, 'success', 200));
    }

    public function getAllRewardItems()
    {
        $items = RewardsItem::with(['item'])
            ->where('is_enable', 1)
            ->groupBy('item_id')
            ->get();
        return response()->json(apiResponseHandler($items, 'success', 200));
    }
}
