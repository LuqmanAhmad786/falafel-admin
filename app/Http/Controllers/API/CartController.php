<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Controller;
use App\Models\AssignAdminReward;
use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\Cart\CartDetail;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\OrderItem;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use App\Models\User\UserMembership;
use App\Models\User\UserRewardItems;
use App\Models\UserRewards;
use App\User;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items',
            'menu_id' => 'required|exists:menus'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $userId = null;
        $userCart = null;
        $itemId = $request->input('item_id');
        $menuId = $request->input('menu_id');
        $deliveryAddress = $request->input('delivery_address');

        // VALIDATE ITEM AVAILABILITY
        $validateItem = Item::find($itemId);
        if($validateItem->is_in_stock == 0){
            return response()->json(apiResponseHandler([], 'This item is unavailable.', 400), 400);
        }
        // VALIDATE REDEEMABLE ITEM

        if (!!$request->user('api')) {
            $userId = $request->user('api')->id;
            $userCart = CartList::where('user_id', '=', $userId)->orderBy('cart_list_id', 'DESC')->first();
        } else if ($request->input('cart_id')) {
            $userId == null;
            $userCart = CartList::where('cart_id', '=', $request->input('cart_id'))->first();
        }

        if($request->input('item_flag') == 1){
            $validate = $this->validateRedeemableItem($itemId,$userId,$userCart->cart_list_id);

            if($validate){
                return response()->json(apiResponseHandler([], $validate, 400), 400);
            }
        }

        $singleMenu = Menu::where('menu_id', '=', $menuId)->first();

        $deliveryFee = 0;
        if($deliveryAddress != ''){
            $deliveryController = new DeliveryController();
            $deliveryRestaurant = Restaurant::where('id',$singleMenu->restaurant_id)->first();
            $deliveryQuote = $deliveryController->generateQuotePostmates($deliveryRestaurant['address'],$deliveryAddress);
            $deliveryFee = $deliveryQuote['fee']/100;
        }
        if ($userCart !== null) {
        } else {
            $userCart = new CartList();
            $userCart->cart_id = generateRandomString();
            $userCart->user_id = $userId;
            $userCart->restaurant_id = $singleMenu->restaurant_id;
            $userCart->total_tax = 0;
            $userCart->order_total = 0;
            $userCart->total_amount = 0;
            $userCart->delivery_fee = $deliveryFee;
            $userCart->save();
        }


        if ($request->input('complete_meal')) {
            if (count($request->input('complete_meal'))) {
                $this->addingMealItem($request->input('complete_meal'), $menuId, $userCart);
            }
        }

        $itemListExist = CartItem::where('cart_list_id', '=', $userCart->cart_list_id)->get();
        $message = null;


        if (!count($itemListExist)) {
            if (false) {
                return response()->json(apiResponseHandler([], $message, 400), 400);
            } else {
                $this->updateCartList($userCart->cart_id, ['menu_id' => $menuId]);
                $modifierGroups = $request->input('menu');
                $mainMenu = Item::where('item_id', '=', $itemId)->first();

                $validateCartItems = CartItem::where('item_id', $itemId)->where('cart_list_id', $userCart->cart_list_id)->get();

                $itemWithAddon = false;

                $addons = [];
                if (sizeof($modifierGroups)) {
                    foreach ($modifierGroups as $modifierGroup) {
                        $addons[] = $modifierGroup['modifier_item_id'];
                    }
                }

                $cartItemId = 0;
                if(count($validateCartItems)){
                    foreach ($validateCartItems AS $validateCartItem){
                        $existingAddons = CartDetail::where('cart_item_id',$validateCartItem['cart_item_id'])->pluck('item_id')->toArray();
                        if(count($existingAddons) == count($addons)){
                            $validateAddons = array_diff($addons, $existingAddons);
                            if(count($validateAddons) == 0){
                                $cartItemId = $validateCartItem['cart_item_id'];
                                $itemWithAddon = true;
                                continue;
                            }
                        }
                    }
                }

                if($itemWithAddon){
                    /*Storing main item in cart*/
                    $cartItem = CartItem::updateOrCreate(['cart_item_id' => $cartItemId,'item_id' => $itemId,'cart_list_id' => $userCart->cart_list_id],[
                        'item_id' => $itemId,
                        'item_count' => DB::raw('item_count + '.$request->input('item_count')),
                        'cart_list_id' => $userCart->cart_list_id,
                        'receiver_name' => '',
                        'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                        'menu_id' => $menuId,
                    ]);
                }
                else{
                    $cartItem = CartItem::create([
                        'item_id' => $itemId,
                        'item_count' => $request->input('item_count'),
                        'cart_list_id' => $userCart->cart_list_id,
                        'receiver_name' => '',
                        'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                        'menu_id' => $menuId,
                    ]);
                    /*Storing modifier items item in cart*/
                    if (sizeof($modifierGroups)) {
                        foreach ($modifierGroups as $modifierGroup) {
                            CartDetail::create([
                                'cart_item_id' => $cartItem->cart_item_id,
                                'modifier_group_id' => $modifierGroup['modifier_id'],
                                'item_id' => $modifierGroup['modifier_item_id'],
                                'item_count' => $modifierGroup['modifier_item_count']
                            ]);
                        }
                    }
                }

                /*Cart amount calculation*/
                cartCalculation($userCart['cart_list_id'], $userId);
                return response()->json(apiResponseHandler($userCart->cart_id, 'Successfully Added In Cart.'));
            }
        } elseif ($itemListExist[0]['menu_id'] == $menuId) {
            if (false) {
                return response()->json(apiResponseHandler([], $message, 400), 400);
            } else {
                $this->updateCartList($userCart->cart_id, ['menu_id' => $menuId]);
                $modifierGroups = $request->input('menu');
                $mainMenu = Item::where('item_id', '=', $itemId)->first();

                $validateCartItems = CartItem::where('item_id', $itemId)->where('cart_list_id', $userCart->cart_list_id)->get();

                $itemWithAddon = false;

                $addons = [];
                if (sizeof($modifierGroups)) {
                    foreach ($modifierGroups as $modifierGroup) {
                        $addons[] = $modifierGroup['modifier_item_id'];
                    }
                }

                $cartItemId = 0;
                if(count($validateCartItems)){
                    foreach ($validateCartItems AS $validateCartItem){
                        $existingAddons = CartDetail::where('cart_item_id',$validateCartItem['cart_item_id'])->pluck('item_id')->toArray();
                        if(count($existingAddons) == count($addons)){
                            $validateAddons = array_diff($addons, $existingAddons);
                            if(count($validateAddons) == 0){
                                $cartItemId = $validateCartItem['cart_item_id'];
                                $itemWithAddon = true;
                                continue;
                            }
                        }
                    }
                }

                if($itemWithAddon){
                    /*Storing main item in cart*/
                    $cartItem = CartItem::updateOrCreate(['cart_item_id' => $cartItemId,'item_id' => $itemId,'cart_list_id' => $userCart->cart_list_id],[
                        'item_id' => $itemId,
                        'item_count' => DB::raw('item_count + '.$request->input('item_count')),
                        'cart_list_id' => $userCart->cart_list_id,
                        'receiver_name' => '',
                        'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                        'menu_id' => $menuId,
                    ]);
                }
                else{
                    $cartItem = CartItem::create([
                        'item_id' => $itemId,
                        'item_count' => $request->input('item_count'),
                        'cart_list_id' => $userCart->cart_list_id,
                        'receiver_name' => '',
                        'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                        'menu_id' => $menuId,
                    ]);
                    /*Storing modifier items item in cart*/
                    if (sizeof($modifierGroups)) {
                        foreach ($modifierGroups as $modifierGroup) {
                            CartDetail::create([
                                'cart_item_id' => $cartItem->cart_item_id,
                                'modifier_group_id' => $modifierGroup['modifier_id'],
                                'item_id' => $modifierGroup['modifier_item_id'],
                                'item_count' => $modifierGroup['modifier_item_count']
                            ]);
                        }
                    }
                }

                /*Cart amount calculation*/
                cartCalculation($userCart['cart_list_id'], $userId);
                return response()->json(apiResponseHandler($userCart->cart_id, 'Successfully Added In Cart.'));
            }
        } elseif ($itemListExist[0]['menu_id'] != $menuId) {
            $singleItem = Item::where('item_id', $itemId)->first();
            if ($singleItem->is_common == 1) {
                $itemCats = ItemCategory::where('item_id', $itemId)->pluck('category_id');
                $menu = MenuCategory::whereIn('category_id', $itemCats)->pluck('menu_id');

                $menuArr = explode(',', str_replace('[', '', str_replace(']', '', $menu)));

                if (in_array($menuId, $menuArr)) {
                    if (false) {
                        return response()->json(apiResponseHandler([], $message, 400), 400);
                    } else {
                        $this->updateCartList($userCart->cart_id, ['menu_id' => $itemListExist[0]['menu_id']]);
                        $modifierGroups = $request->input('menu');
                        $mainMenu = Item::where('item_id', '=', $itemId)->first();

                        $validateCartItems = CartItem::where('item_id', $itemId)->where('cart_list_id', $userCart->cart_list_id)->get();

                        $itemWithAddon = false;

                        $addons = [];
                        if (sizeof($modifierGroups)) {
                            foreach ($modifierGroups as $modifierGroup) {
                                $addons[] = $modifierGroup['modifier_item_id'];
                            }
                        }

                        $cartItemId = 0;
                        if(count($validateCartItems)){
                            foreach ($validateCartItems AS $validateCartItem){
                                $existingAddons = CartDetail::where('cart_item_id',$validateCartItem['cart_item_id'])->pluck('item_id')->toArray();
                                if(count($existingAddons) == count($addons)){
                                    $validateAddons = array_diff($addons, $existingAddons);
                                    if(count($validateAddons) == 0){
                                        $cartItemId = $validateCartItem['cart_item_id'];
                                        $itemWithAddon = true;
                                        continue;
                                    }
                                }
                            }
                        }

                        if($itemWithAddon){
                            /*Storing main item in cart*/
                            $cartItem = CartItem::updateOrCreate(['cart_item_id' => $cartItemId,'item_id' => $itemId,'cart_list_id' => $userCart->cart_list_id],[
                                'item_id' => $itemId,
                                'item_count' => DB::raw('item_count + '.$request->input('item_count')),
                                'cart_list_id' => $userCart->cart_list_id,
                                'receiver_name' => '',
                                'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                                'menu_id' => $menuId,
                            ]);
                        }
                        else{
                            $cartItem = CartItem::create([
                                'item_id' => $itemId,
                                'item_count' => $request->input('item_count'),
                                'cart_list_id' => $userCart->cart_list_id,
                                'receiver_name' => '',
                                'item_flag' => $request->input('item_flag') ? $request->input('item_flag') : 0,
                                'menu_id' => $menuId,
                            ]);
                            /*Storing modifier items item in cart*/
                            if (sizeof($modifierGroups)) {
                                foreach ($modifierGroups as $modifierGroup) {
                                    CartDetail::create([
                                        'cart_item_id' => $cartItem->cart_item_id,
                                        'modifier_group_id' => $modifierGroup['modifier_id'],
                                        'item_id' => $modifierGroup['modifier_item_id'],
                                        'item_count' => $modifierGroup['modifier_item_count']
                                    ]);
                                }
                            }
                        }

                        /*Cart amount calculation*/
                        cartCalculation($userCart['cart_list_id'], $userId);
                        return response()->json(apiResponseHandler($userCart->cart_id, 'Successfully Added In Cart.'));
                    }
                }
            }
            return response()->json(apiResponseHandler([], 'You can not add item from any other menu type.', 400), 400);
        }
    }

    public function validateRedeemableItem($itemId,$userId,$cartId){
        $cartItemExist = CartItem::where('cart_list_id',$cartId)
            ->where('item_flag',1)
            ->count();
        if($cartItemExist > 0){
            return 'You can\'t add more than one reward item in cart.';
        }
        $cartItemExist = CartItem::where('item_id',$itemId)
                            ->where('cart_list_id',$cartId)
                            ->where('item_flag',1)
                            ->count();
        if($cartItemExist){
            return 'This item already exists in cart';
        }
        $rewardItem = RewardsItem::where('item_id',$itemId)->first();

        $userRewardPoints = UserRewards::where('user_id',$userId)->sum('total_rewards');

        if($rewardItem->points_required > $userRewardPoints){
            return 'You don\'t have sufficient points to redeem this items.';
        }

        $userRewardItems = UserRewardItems::where('user_id',$userId)->pluck('reward_item_id')->toArray();

        if(in_array($rewardItem->reward_item_id,$userRewardItems)){
            return 'You already redeemed this item. Please redeem all other items to redeem this item again.';
        }

        $userMembership = UserMembership::where('user_id',$userId)->first();

        $membershipId = $userMembership->membership_id;

        if($rewardItem->is_for_gold_only == 1 && $membershipId != 3){
            return 'This item for gold members only';
        }

        $userRewardPoints = UserRewards::where('user_id',$userId)->sum('total_rewards');

        if($userRewardPoints < $rewardItem->points_required){
            return 'You Don\'t have sufficient points to redeem this item.';
        }

        return false;
    }

    public function addingMealItem($mealArray, $menuId, $userCart)
    {
        foreach ($mealArray as $value) {
            CartItem::create([
                'item_id' => $value,
                'cart_list_id' => $userCart['cart_list_id'],
                'receiver_name' => '',
                'item_flag' => 4,
                'menu_id' => $menuId,
            ]);
        }
        /*Cart amount calculation*/
        cartCalculation($userCart['cart_list_id']);
    }

    public function updateCartList($cartId, $data)
    {
        CartList::where('cart_id', '=', $cartId)->update($data);
        return CartList::where('cart_id', '=', $cartId)->first();
    }

    public function getCart(Request $request)
    {
        if (!!$request->user('api')) {
            $cartList = CartList::where('user_id', '=', $request->user('api')->id)->pluck('cart_list_id');
            $cartId = CartList::where('user_id', '=', $request->user('api')->id)
                ->orderBy('cart_list_id', 'DESC')->first();
            $cartId = $cartId['cart_id'];
            $cartList = CartList::where('cart_id', '=', $cartId)->pluck('cart_list_id');
        } else {
            $validator = Validator::make($request->all(), [
                'cart_id' => 'required|exists:cart_lists,cart_id',
            ]);

            if ($validator->fails()) {
                return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
            }

            $cartList = CartList::where('cart_id', '=', $request->get('cart_id'))->pluck('cart_list_id');
            $cartId = $request->get('cart_id');
        }
        $cartItemIds = CartItem::whereIn('cart_list_id',$cartList)->pluck('cart_item_id');
        $mdIds = CartDetail::whereIn('cart_item_id',$cartItemIds)->groupBy('modifier_group_id')->pluck('modifier_group_id');
        Session::put('mdIds',$mdIds);
        $response['cart_data'] = (new CartItem())
            ->with(['details' => function ($query) {
                $query->leftJoin('modifier_items', 'modifier_items.id', 'cart_details.item_id');
            }])
            ->leftJoin('items', 'items.item_id', 'cart_items.item_id')
            ->whereIn('cart_list_id', $cartList)
            ->get();

        $response['cart_details'] = CartList::where('cart_id', '=', $cartId)
            ->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'cart_lists.coupon_id')
            ->select(
                'cart_lists.cart_list_id',
                'cart_lists.user_id',
                'cart_lists.cart_id',
                DB::raw('FORMAT(cart_lists.total_tax,2) as total_tax'),
                DB::raw('FORMAT(cart_lists.order_total,2) as order_total'),
                DB::raw('FORMAT(cart_lists.total_amount,2) as total_amount'),
                'cart_lists.menu_id',
                'cart_lists.restaurant_id',
                'cart_lists.discount_amount',
                'cart_lists.delivery_fee',
                'cart_lists.coupon_id',
                'cart_lists.bonus_id',
                'reward_coupons.expiry',
                'reward_coupons.coupon_type'
            )
            ->first();

        if (!!$request->user('api')) {
            $is_user = User::where('id', '=', $request->user('api')->id)
                ->select('date_of_birth', DB::raw('DATE_FORMAT(FROM_UNIXTIME(date_of_birth), "%d-%m") as date_of_birth'))
                ->get();

            $is_birthday = RewardCoupon::where('user_id', '=', $request->user('api')->id)
                ->where('coupon_type', '=', 2)
                ->get();

            $is_point = RewardCoupon::where('user_id', '=', $request->user('api')->id)
                ->where('status', '=', 1)
                ->where('coupon_type', '=', 1)
                ->get();

            $is_reward_item = (new CartItem())
                ->leftJoin('items', 'items.item_id', 'cart_items.item_id')
                ->whereIn('cart_list_id', $cartList)
                ->whereIn('item_flag', [1, 3])
                ->get();

            $isAdminReward = AssignAdminReward::where('user_id', '=', $request->user('api')->id)->get();
            $isBonusFound = BonusAppliedFor::leftJoin('bonus', 'bonus.bonus_id', 'bonus_applied_for.bonus_id')
            ->where('user_id', '=', $request->user('api')->id)
            ->where('bonus_type','!=','1')
            ->where('is_used', '=', 0)->count();

            $response['cart_details']['is_birthday'] =
                (date('d-m') == $is_user[0]['date_of_birth'])
                && (sizeof($is_birthday) && $is_birthday[0]['status'] == 1)
                    ? 1 : 0;

            $response['cart_details']['is_reward_coupon'] = count($is_point) ? 1 : 0;
            $response['cart_details']['is_admin_reward'] = count($isAdminReward) ? 1 : 0;
            $response['cart_details']['is_reward_added'] = count($is_reward_item) ? 1 : 0;
            $response['cart_details']['is_bonus'] = $isBonusFound ? 1 : 0;
            if (sizeof($is_reward_item)) {
                $response['cart_details']['is_reward_item'] = $is_reward_item[0];
            }
        } else {
            $response['cart_details']['is_birthday'] = 0;
            $response['cart_details']['is_reward_coupon'] = 0;
            $response['cart_details']['is_admin_reward'] = 0;
            $response['cart_details']['is_bonus'] = 0;
        }
        Session::forget('mdIds');
        return response()->json(apiResponseHandler($response, 'success'));
    }

    public function removeCart($itemId, Request $request)
    {
        if (!!$request->user('api')) {
            $userId = $request->user('api')->id;
        } else {
            $userId = null;
        }

        $cartItem = CartItem::where('cart_item_id', '=', $itemId)->first();
        CartItem::where('cart_item_id', '=', $itemId)->delete();
        CartDetail::where('cart_item_id', '=', $itemId)->delete();

//        $anyItemExist = CartItem::where('cart_list_id', '=', $cartItem['cart_list_id'])
//            ->where('item_flag', '!=', 4)
//            ->get();

//        if (!sizeof($anyItemExist)) {
//            CartItem::where('cart_list_id', '=', $cartItem['cart_list_id'])
//                ->where('item_flag', '=', 4)
//                ->delete();
//        }

        cartCalculation($cartItem['cart_list_id'], $userId);
        return response()->json(apiResponseHandler([], 'Item Removed Successfully'));
    }

    public function emptyCart(Request $request)
    {
        if (!!$request->user('api')) {
            $cartList = CartList::where('user_id', '=', $request->user('api')->id)->pluck('cart_list_id');
        } else {
            $validator = Validator::make($request->all(), [
                'cart_id' => 'required|exists:cart_lists,cart_id',
            ]);
            if ($validator->fails()) {
                return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
            }
            $cartList = CartList::where('cart_id', '=', $request->get('cart_id'))->pluck('cart_list_id');
        }

        $cartItems = CartItem::where('cart_list_id', $cartList)->pluck('cart_item_id');

        if (sizeof($cartItems)) {
            CartDetail::whereIn('cart_item_id', $cartItems)->delete();
        }

        if (sizeof($cartList)) {
            CartItem:: whereIn('cart_list_id', $cartList)->delete();
        }

        CartList::whereIn('cart_list_id', $cartList)->update([
            'order_total' => 0,
            'total_amount' => 0,
            'total_tax' => 0
        ]);

        return response()->json(apiResponseHandler([], 'Cart Empty.'));
    }

    public function emptyCartGuest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:cart_lists,cart_id',
        ]);
        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }
        $cartList = CartList::where('cart_id', '=', $request->get('cart_id'))->pluck('cart_list_id');

        $cartItems = CartItem::where('cart_list_id', $cartList)->pluck('cart_item_id');

        if (sizeof($cartItems)) {
            CartDetail::whereIn('cart_item_id', $cartItems)->delete();
        }

        if (sizeof($cartList)) {
            CartItem:: whereIn('cart_list_id', $cartList)->delete();
        }

        CartList::whereIn('cart_list_id', $cartList)->update([
            'order_total' => 0,
            'total_amount' => 0,
            'total_tax' => 0
        ]);

        return response()->json(apiResponseHandler([], 'Cart Empty.'));
    }

    public function duplicateCart(Request $request)
    {
        $userId = null;
        if (!!$request->user('api')) {
            $userId = $request->user('api')->id;
        }
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|exists:cart_items',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $cartItem = CartItem::find($request->input('cart_item_id'))->replicate();
        $cartItem->save();

        $cartDetails = CartDetail::where('cart_item_id', '=', $request->input('cart_item_id'))->get();
        foreach ($cartDetails as $cartDetail) {
            $cartDetailNew = CartDetail::find($cartDetail->id)->replicate();
            $cartDetailNew->cart_item_id = $cartItem->cart_item_id;
            $cartDetailNew->save();
        }

        cartCalculation($cartItem['cart_list_id'],$userId);

        return response()->json(apiResponseHandler([], 'Added Duplicate Successfully'));
    }

    public function updateCart(Request $request)
    {
        $userId = null;
        if (!!$request->user('api')) {
            $userId = $request->user('api')->id;
        }
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|exists:cart_items,cart_item_id',
            'menu' => 'required|array',
            'menu.*.modifier_id' => 'required',
            'menu.*.modifier_item_id' => 'required',
            'menu.*.modifier_item_count' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $cartItemId = $request->input('cart_item_id');
        $cartItem = CartItem::where('cart_item_id', '=', $cartItemId)->first();
        CartDetail::where('cart_item_id', '=', $cartItemId)->delete();
        CartList::where('cart_list_id', '=', $cartItem['cart_list_id'])->update([
            'total_tax' => 0,
            'order_total' => 0,
            'total_amount' => 0
        ]);

        $modifierGroups = $request->input('menu');
        foreach ($modifierGroups as $modifierGroup) {
            CartDetail::create([
                'cart_item_id' => $cartItemId,
                'modifier_group_id' => $modifierGroup['modifier_id'],
                'item_id' => $modifierGroup['modifier_item_id'],
                'item_count' => $modifierGroup['modifier_item_count']
            ]);
        }

        $cartItem = CartItem::where('cart_item_id', '=', $cartItemId)->first();

        cartCalculation($cartItem['cart_list_id'],$userId);

        return response()->json(apiResponseHandler([], 'Successfully Updated Cart.'));
    }

    public function reOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        $OrderMenuId = Order::where('order_id',$request->input('order_id'))->first();

        $response = Menu::where('menu_id', '=', $OrderMenuId['menu_id'])->first();
        $response['from'] = strtotime($response['from']);
        $response['to'] = strtotime($response['to']);
        $currentDayName = date('l');
        $message = null;

        if ($currentDayName == 'Saturday' && $response['menu_name'] == 'Breakfast') {
            $message = $response['menu_saturday_message'];
        }
        elseif ($currentDayName == 'Sunday' && $response['menu_name'] == 'Breakfast') {
            $message = $response['menu_resume_time_message'];
        }
        else {
            $message = $response['menu_resume_time_message'];
        }

        if ($response['to'] < time()) {
            return response()->json(apiResponseHandler($response, $message, 400), 400);
        }

        $myCart = CartList::where('user_id', '=', Auth::user()->id)->orderBy('cart_list_id', 'DESC')->first();
        $orderTotal = $myCart['order_total'];
        $totalTax = $myCart['total_tax'];
        $getMainItem = OrderDetail::where('order_id', $request->input('order_id'))->get();

        foreach ($getMainItem as $main) {
            $cartItem = new CartItem();
            $cartItem->item_id = $main['item_id'];
            $cartItem->item_count = $main['item_count'];
            $cartItem->cart_list_id = $myCart['cart_list_id'];
            $cartItem->save();

            $mainMenu = Item::where('item_id', '=', $main['item_id'])->first();
            $orderTotal += $mainMenu['item_price'];
            $totalTax += $mainMenu['tax_rate'];

            $getSubItem = OrderItem::where('order_detail_id', '=', $main['order_detail_id'])->get();
            foreach ($getSubItem as $sub) {
                $cartDetails = new CartDetail();
                $cartDetails->cart_item_id = $cartItem->cart_item_id;
                $cartDetails->modifier_group_id = $sub['modifier_group_id'];
                $cartDetails->item_id = $sub['item_id'];
                $cartDetails->item_count = $sub['item_count'];
                $cartDetails->save();
                $singleItem = Item::where('item_id', '=', $sub['item_id'])->first();
                $orderTotal += $sub['item_count'] * $singleItem['item_price'];
                $totalTax += $sub['item_count'] * $singleItem['tax_rate'];
            }

            CartList::where('cart_list_id', '=', $myCart['cart_list_id'])->update([
                'total_tax' => $totalTax,
                'order_total' => $orderTotal,
                'total_amount' => $totalTax + $orderTotal
            ]);
        }
        cartCalculation($myCart['cart_list_id'],Auth::user()->id);
        return response()->json(apiResponseHandler([], 'Successfully Added.'));
    }

    public function getTiming(Request $request)
    {
        if ($request->filled('menu_id')) {
            $userCart = Menu::where('menu_id', '=', $request->input('menu_id'))->first();
        } else if ($request->user('api')) {
            $userCart = (new CartList())
                ->leftJoin('menus', 'menus.menu_id', '=', 'cart_lists.menu_id')
                ->where('user_id', '=', $request->user('api')->id)
                ->whereNotNull('menus.menu_id')
                ->orderBy('cart_list_id', 'DESC')
                ->first();
        } else {
            return response()->json(apiResponseHandler([]));
        }

        $delivery = 0;
        if ($request->filled('preference') && $request->input('preference') == 'delivery') {
            $delivery = 3600;
        }
        $restaurant = Restaurant::find($userCart->restaurant_id);
        Config::set('app.timezone', $restaurant->timezone);
        date_default_timezone_set($restaurant->timezone);
        $userCart->from = strtotime($userCart->from)+$delivery;
        $userCart->to = strtotime($userCart->to);
        if($userCart->from_2 && $userCart->to_2){
            $userCart->from_2 = strtotime($userCart->from_2)+$delivery;
            $userCart->to_2 = strtotime($userCart->to_2);
        }
        $userCart->time_now = time();

        if ($request->filled('date') && $request->input('date') != '') {
            if(date('Y-m-d') != date('Y-m-d',$request->input('date'))){
                $userCart->time_now = strtotime('10:30:00');
            }
        }
        $userCart->time_interval = $restaurant->time_interval*60;

        return response()->json(apiResponseHandler($userCart));
    }

    public function applyReward($couponId)
    {
        $cart = CartList::where('user_id', '=', Auth::user()->id)
            ->orderBy('cart_lists.cart_list_id', 'DESC')
            ->first();

        if (!$cart) {
            $cart = new CartList();
            $cart->cart_id = generateRandomString();
            $cart->user_id = Auth::user()->id;
            $cart->total_tax = 0;
            $cart->order_total = 0;
            $cart->total_amount = 0;
            $cart->save();
        }else{
            $this->removeBonus();
            $this->removeReward();
        }

        CartList::where('cart_lists.cart_id', '=', $cart['cart_id'])->update(['coupon_id' => $couponId]);
        RewardCoupon::where('reward_coupons.coupon_id', '=', $couponId)->update(['status' => 3]);
        cartCalculation($cart['cart_list_id'], Auth::user()->id);
        return response()->json(apiResponseHandler([], 'Applied successfully'), 200);
    }

    public function applyBonus($bonusId)
    {
        $cart = CartList::where('user_id', '=', Auth::user()->id)
            ->orderBy('cart_lists.cart_list_id', 'DESC')
            ->first();
        if (!$cart) {
            $cart = new CartList();
            $cart->cart_id = generateRandomString();
            $cart->user_id = Auth::user()->id;
            $cart->total_tax = 0;
            $cart->order_total = 0;
            $cart->total_amount = 0;
            $cart->save();
        }else{
            $this->removeBonus();
            $this->removeReward();
        }

        CartList::where('cart_lists.cart_id', '=', $cart['cart_id'])->update(['bonus_id' => $bonusId]);
        $appliedBonus = BonusAppliedFor::where('bonus_id',$bonusId)->first();
        if($appliedBonus->is_used == 1){
            return response()->json(apiResponseHandler([], 'Bonus already used.'), 400);
        }

        $isBonus = Bonus::where('bonus_id', '=', $bonusId)->first();
        if($isBonus && $isBonus['bonus_type'] == 3) {
            $cartList = null;
            $cartList = CartList::where('cart_id', '=', $cart['cart_id'])->first();
            $is_bonus_exist = BonusAppliedFor::where('user_id', '=', Auth::user()->id)->get();
            if (count($is_bonus_exist)) {
                CartItem::create([
                    'item_id' => $isBonus['bonus_free_item_id'],
                    'cart_list_id' => $cartList->cart_list_id,
                    'receiver_name' => '',
                    'item_flag' => 5,
                    'bonus_id' => $isBonus['bonus_id']
                ]);
                $item = Item::where('item_id', $isBonus['bonus_free_item_id'])->first();
                CartList::where('cart_id', '=', $cart['cart_id'])->update([
                    'discount_amount' => $item->item_price
                ]);
            }
        }

        BonusAppliedFor::where('bonus_id', '=', $bonusId)->where('user_id', '=', Auth::user()->id)->update(['is_used' => 2]);
        cartCalculation($cart['cart_list_id'], Auth::user()->id);
        return response()->json(apiResponseHandler([], 'Applied successfully'), 200);
    }

    public function removeReward()
    {
        $cart = CartList::where('user_id', '=', Auth::user()->id)
            ->orderBy('cart_lists.cart_list_id', 'DESC')
            ->first();

        RewardCoupon::where('reward_coupons.coupon_id', '=', $cart['coupon_id'])->update(['status' => 1]);
        CartList::where('cart_lists.cart_id', '=', $cart['cart_id'])->update(['coupon_id' => null]);

        cartCalculation($cart['cart_list_id']);
        return response()->json(apiResponseHandler([], 'Removed successfully'), 200);
    }

    public function removeBonus()
    {
        $cart = CartList::where('user_id', '=', Auth::user()->id)
            ->orderBy('cart_lists.cart_list_id', 'DESC')
            ->first();

        BonusAppliedFor::where('bonus_id', '=', $cart['bonus_id'])->update(['is_used' => 0]);
        CartList::where('cart_lists.cart_id', '=', $cart['cart_id'])->update(['bonus_id' => null]);
        $isBonus = Bonus::where('bonus_id', '=', $cart['bonus_id'])->first();
        if($isBonus && $isBonus['bonus_type'] == 3) {
            $cartList = null;
            $cartList = CartList::where('cart_id', '=', $cart['cart_id'])->first();
            $is_bonus_exist = BonusAppliedFor::where('user_id', '=', Auth::user()->id)->get();
            if (count($is_bonus_exist)) {
                CartItem::where('bonus_id',$cart['bonus_id'])->delete();
                CartList::where('cart_id', '=', $cart['cart_id'])->update([
                    'discount_amount' => 0
                ]);
            }
        }
        cartCalculation($cart['cart_list_id']);
        return response()->json(apiResponseHandler([], 'Removed successfully'), 200);
    }

    public function resetDeliveryFee(Request $request){
        CartList::where('cart_id', $request->input('cart_id'))->update(['delivery_fee'=>0]);
        $cart = CartList::where('cart_id', $request->input('cart_id'))->first();
        cartCalculation($cart['cart_list_id']);
        return response()->json(apiResponseHandler([], 'Removed successfully'), 200);
    }

    public function increaseCartQuantity(Request $request){
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|exists:cart_items',
            'item_count' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        CartItem::where('cart_item_id', $request->input('cart_item_id'))->update(['item_count' => $request->input('item_count')]);
        $cartItem = CartItem::where('cart_item_id', $request->input('cart_item_id'))->first();
        cartCalculation($cartItem->cart_list_id);
        return response()->json(apiResponseHandler([], 'Quantity updated successfully'), 200);
    }

    public function validateCartItems(Request $request){
        $cart = CartList::where('cart_id',$request->input('cart_id'))->first();
        $cartItems = CartItem::where('cart_list_id', '=', $cart->cart_list_id)->get();
        $unavailableItems = [];
        if (count($cartItems)) {
            foreach ($cartItems as $item) {
                $itemStatus = Item::find($item->item_id);
                if($itemStatus->is_in_stock == 0){
                    array_push($unavailableItems,$item['item_id']);
                }
            }
        }

        if(count($unavailableItems) > 0){
            return response()->json(apiResponseHandler([], 'Some of the cart items are out of stock now. Please remove to continue checkout.', 401), 401);
        }
        return response()->json(apiResponseHandler([], '', 200), 200);
    }

    public function validateRestaurantItems(Request $request){
        $cartId = $request->input('cart_id');
        $restaurantId = $request->input('restaurant_id');
        $cart = CartList::where('cart_id',$cartId)->first();
        $existingRestaurant = $cart->restaurant_id;
        $cartItems = CartItem::where('cart_list_id', '=', $cart->cart_list_id)->get();
        $unavailableItems = [];
        if (count($cartItems)) {
            foreach ($cartItems as $item) {
                $itemStatus = Item::where('item_id',$item->item_id)
                    ->where('restaurant_id',$existingRestaurant)
                    ->first();

                if($itemStatus){
                    $otherResItem = Item::where('item_name',$itemStatus->item_name)
                        ->where('restaurant_id',$restaurantId)
                        ->first();
                    if($otherResItem){
                        if($otherResItem->is_in_stock == 0){
                            array_push($unavailableItems,$itemStatus['item_id']);
                        }
                    }else{
                        array_push($unavailableItems,$itemStatus['item_id']);
                    }
                }
            }
        }

        if(count($unavailableItems) > 0){
            $itemNames = Item::whereIn('item_id',$unavailableItems)->pluck('item_name');
            return response()->json(apiResponseHandler([], 'Some of the cart items are not available with the location you are selecting. Clicking continue will automatically delete the unavailable items. Unavailable Item(s): '.$itemNames, 401), 401);
        }
        return response()->json(apiResponseHandler([], '', 200), 200);
    }

    public function clearUnavailableItems(Request $request){
        $cartId = $request->input('cart_id');
        $restaurantId = $request->input('restaurant_id');
        $cart = CartList::where('cart_id',$cartId)->first();
        $existingRestaurant = $cart->restaurant_id;
        $cartItems = CartItem::where('cart_list_id', '=', $cart->cart_list_id)->get();
        $unavailableItems = [];
        if (count($cartItems)) {
            foreach ($cartItems as $item) {
                $itemStatus = Item::where('item_id',$item->item_id)
                    ->where('restaurant_id',$existingRestaurant)
                    ->first();

                if($itemStatus){
                    $otherResItem = Item::where('item_name',$itemStatus->item_name)
                        ->where('restaurant_id',$restaurantId)
                        ->first();
                    if($otherResItem){
                        if($otherResItem->is_in_stock == 0){
                            array_push($unavailableItems,$item['cart_item_id']);
                        }
                    }else{
                        array_push($unavailableItems,$item['cart_item_id']);
                    }
                }
            }
        }

        if(count($unavailableItems) > 0){
            CartItem::whereIn('cart_item_id',$unavailableItems)->delete();
            CartDetail::whereIn('cart_item_id',$unavailableItems)->delete();
        }
        return response()->json(apiResponseHandler([], '', 200), 200);
    }
}

