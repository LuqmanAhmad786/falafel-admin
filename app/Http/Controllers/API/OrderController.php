<?php

namespace App\Http\Controllers\API;

use Alvee\WorldPay\lib\Worldpay;
use Alvee\WorldPay\lib\WorldpayException;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentProcessorController;
use App\Models\AdminReward;
use App\Models\AssignAdminReward;
use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\Cart\CartDetail;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\DevicePreference;
use App\Models\Favorite\FavoriteLabel;
use App\Models\Favorite\FavoriteOrder;
use App\Models\FirebaseToken;
use App\Models\Item;
use App\Models\ManageNotifications;
use App\Models\Menu;
use App\Models\ModifierGroup;
use App\Models\ModifierItems;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\OrderItem;
use App\Models\OrderPayment;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use App\Models\SavedCard;
use App\Models\SubscriptionPreference;
use App\Models\User\Membership;
use App\Models\User\UserMembership;
use App\Models\User\UserRewardItems;
use App\Models\UserRewards;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class OrderController extends Controller
{
    public function confirmOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => [
                'required',
                Rule::exists('cart_lists', 'cart_id')
            ],
            'restaurant_id' => 'required',
            'pickup_time' => 'required',
            'pickup_date' => 'required',
            'preference' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $selectedRestaurant = Restaurant::where('id', '=', $request->input('restaurant_id'))->first();

        if ($selectedRestaurant['is_opened'] == 1) {
            $cartList = CartList::where('cart_id', '=', $request->input('cart_id'))->get();

            if ($cartList[0]['total_amount'] > 0) {
                if (!$request->input('payment_data')) {
                    $validator = Validator::make($request->all(), [
                        'selected_card' => [
                            'required',
                            Rule::exists('saved_cards', 'id')->where('user_id', Auth::user()->id)
                        ],
                    ]);

                    if ($validator->fails()) {
                        //return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
                    }
                }
            }

            $orderTotal = 0;
            $totalTax = 0;
            $cartItems = null;
            $cartDetails = null;
            $singleModifier = null;
            $bonusType = 0;

            $cartItems = CartItem::where('cart_list_id', '=', $cartList[0]['cart_list_id'])->get();

            $totalRedeemedPoints = 0;
            $appliedRewardItems = [];

            if (count($cartItems)) {
                foreach ($cartItems as $item) {
                    // COUNT POINTS FOR FREE ITEMS
                    if($item['item_flag'] == 1){
                        $rewardItem = RewardsItem::where('item_id',$item['item_id'])->first();
                        if($rewardItem){
                            $totalRedeemedPoints += $rewardItem->points_required;
                            array_push($appliedRewardItems,$rewardItem->reward_item_id);
                        }
                    }
                }
            }

            $userRewardPoints = UserRewards::where('user_id',Auth::user()->id)->sum('total_rewards');

            if($totalRedeemedPoints > $userRewardPoints){
                return response()->json(apiResponseHandler([], 'You don\'t have sufficient points to redeem cart items.', 400), 400);
            }

            $cartData = cartCalculation($cartList[0]['cart_list_id']);

            if ($cartData['total_amount'] == 0 && $totalRedeemedPoints > 0) {
                return response()->json(apiResponseHandler([], 'Make sure to add a paid item to take advantage of reward item(s)!', 400), 400);
            }

            $selectedMenu = Menu::where('restaurant_id', '=', $request->input('restaurant_id'))
                ->where('menu_id', '=', $cartData['menu_id'])
                ->first();

            $agent = new Agent();
            $orderDevice = 1;
            if ($agent->isMobile()) {
                $orderDevice = 2;
            }

            $order = new Order();
            $order->reference_id = generateRefId();/*$order->reference_id = date('Y') . time()*/;
            $order->user_id = Auth::user()->id;
            $order->restaurant_id = $request->input('restaurant_id');
            $order->pickup_time = $request->input('pickup_time');
            $order->pickup_date = date('Y-m-d', $request->input('pickup_date')+25200);
            $order->preparation_time = $request->input('pickup_time') - $selectedRestaurant['preparation_time'];
            $order->order_total = $cartData['order_total'];
            $order->total_tax = $cartData['total_tax'];
            $order->total_amount = $cartData['total_amount'];
            $order->discount_amount = $cartData['discount_amount'];
            $order->delivery_fee = $cartData['delivery_fee'];
            $order->coupon_id = $cartData['coupon_id'];
            $order->menu_id = $cartData['menu_id'];
            $order->user_first_name = Auth::user()->first_name;
            $order->user_last_name = Auth::user()->last_name;
            $order->user_email = Auth::user()->email;
            $order->user_number = Auth::user()->mobile;
            $order->order_type = $request->input('preference') == 'pickup' ? 1 : 2;
            $order->bonus_id = $request->input('bonus_id') ? $request->input('bonus_id') : 0;
            $order->order_device = $orderDevice;
            $order->payment_status = 3;
            if($request->input('preference') === 'delivery'){
                $order->delivery_address = $request->input('delivery_address');
                $order->delivery_notes = $request->input('delivery_notes');
            }
            $order->save();

            $pointMultiply = 1;
            if ($cartList[0]['total_amount'] > 0) {
                if($request->input('selected_cc_id') || $request->has('payment_data')){
                    // Commented by deven On 14 APR 2022
                    if($request->has('payment_data')){
                        $result = app('App\Http\Controllers\API\StripePaymentController')->walletPaymentWithToken($order, $request->input('payment_data'));
                    }elseif($request->input('selected_cc_id')){
                        $result = app('App\Http\Controllers\API\StripePaymentController')->makeOrderPayment($request->input('selected_cc_id'),$order);
                    }
                    Log::debug($result);
                    if (!$result) {
                        Order::where('order_id', '=', $order->order_id)->delete();
                        Log::debug('card');
                        return response()->json(apiResponseHandler([], 'Something went wrong with payment. Please try again.', 400), 400);
                    }else{
                        Log::debug('false');
                    }
                }
                elseif ($request->has('selected_card')){
                    $walletController = new WalletController();

                    $validateCardBalance = $walletController->validateCardBalance($request->input('selected_card'), $cartList[0]['total_amount']);

                    if($validateCardBalance){
                        $result = $walletController->makeOrderPayment($request->input('selected_card'), $cartList[0]['total_amount'], $order->order_id);
                        $pointMultiply = 2;
                        if (!$result) {
                            Order::where('order_id', '=', $order->order_id)->delete();
                            return response()->json(apiResponseHandler([], 'Something went wrong falafel card payment. Please try again.', 400), 400);
                        }
                    }else{
                        return response()->json(apiResponseHandler([], 'Insufficient balance in card to place this order.', 400), 400);
                    }
                }
            }

            /*update reward coupon*/
            if ($cartList[0]['coupon_id']) {
                RewardCoupon::where('coupon_id', '=', $cartList[0]['coupon_id'])->update([
                    'status' => 2
                ]);
            }

            Order::where('order_id', '=', $order->order_id)->update(['payment_status' => 1]);

            if (count($cartItems)) {
                foreach ($cartItems as $item) {
                    $menuPrice = Item::where('item_id', '=', $item['item_id'])->first();
                    $orderDetails = new OrderDetail();
                    $orderDetails->order_id = $order->order_id;
                    $orderDetails->item_id = $item['item_id'];
                    $orderDetails->item_count = $item['item_count'];
                    $orderDetails->item_price = $menuPrice['item_price'];
                    $orderDetails->item_name = $menuPrice['item_name'];
                    $orderDetails->item_image = $menuPrice['item_image'];
                    $orderDetails->item_description = $menuPrice['item_description'];
                    $orderDetails->item_flag = $item['item_flag'];
                    $orderDetails->save();

                    $cartDetails = CartDetail::where('cart_item_id', '=', $item['cart_item_id'])->get();

                    if (count($cartDetails)) {
                        foreach ($cartDetails as $details) {
                            $singleModifier = ModifierGroup::where('modifier_group_id', '=', $details['modifier_group_id'])->first();
                            if ($details['count'] > $singleModifier['single_item_maximum']) {
                                return response()->json(apiResponseHandler([], 'You can\'t add more than' . ' ' . $singleModifier['single_item_maximum'] . ' item from' . ' ' . $singleModifier['modifier_group_name'], 400), 400);
                            } else {
                                $itemPrice = ModifierItems::where('id', '=', $details['item_id'])->first();
                                $orderModifier = new OrderItem();
                                $orderModifier->order_detail_id = $orderDetails->id;
                                $orderModifier->modifier_group_id = $details['modifier_group_id'];
                                $orderModifier->item_id = $itemPrice['id'];
                                $orderModifier->item_count = $details['item_count'];
                                $orderModifier->item_price = $itemPrice['item_price'];
                                $orderModifier->item_name = $itemPrice['item_name'];
                                $orderModifier->item_image = $itemPrice['item_image'];
                                $orderModifier->item_description = $itemPrice['item_description'];
                                $orderModifier->save();
                            }
                        }
                    }
                }
            } else {
                return response()->json(apiResponseHandler([], 'Cart Details not found', 400), 400);
            }

            if($totalRedeemedPoints > 0){
                UserRewards::create([
                    'order_id' => $order->order_id,
                    'user_id' => Auth::user()->id,
                    'total_rewards' => -$totalRedeemedPoints,
                    'month' => strtotime(date('Y-m', time()) . '-1'),
                    'type' => 2
                ]);
                createCardTrxHistory([
                    'falafel_card_id' => 0,
                    'action_type' => $totalRedeemedPoints . ' Points Redeemed',
                    'transaction_amount' => $order->total_amount
                ]);

                if(count($appliedRewardItems) > 0){
                    foreach ($appliedRewardItems AS $item){
                        UserRewardItems::create([
                           'reward_item_id' => $item,
                           'user_id' => Auth::user()->id
                        ]);
                    }
                }
            }

            $this->resetUserRewards();

            $totalRewards = 0;
            if ($cartData['total_amount'] > 0) {
                $totalRewards = round($cartData['order_total'],2);
            }
            $response['order_id'] = $order->order_id;
            $response['total_rewards'] = $totalRewards;

            if ($request->filled('bonus_id')) {
                $isApplied = BonusAppliedFor::where('bonus_id', '=', $request->input('bonus_id'))
                    ->where('user_id', '=', Auth::user()->id)
                    ->get();
                if (sizeof($isApplied)) {
                    $isBonus = Bonus::where('bonus_id', '=', $request->input('bonus_id'))->first();
                    if ($isBonus['bonus_type'] == 2) {
                        $bonusType = $isBonus['bonus_type'];
                        //$this->applyBonus($isBonus, $request->input('cart_id'), $response);
                        UserRewards::create([
                            'order_id' => $order->order_id,
                            'user_id' => Auth::user()->id,
                            'total_rewards' => $isBonus['bonus_points_multiplier'] * $totalRewards,
                            'month' => strtotime(date('Y-m', time()) . '-1'),
                            'type' => 1,
                        ]);
                        BonusAppliedFor::where('user_id', '=', Auth::user()->id)->where('bonus_id', $isBonus['bonus_id'])->update(['is_used' => 1]);
                    }
                }
            }

            if ($bonusType != 2) {
                if ($totalRewards > 0) {
                    $totalRewards = $totalRewards*$pointMultiply;
                    UserRewards::create([
                        'order_id' => $order->order_id,
                        'user_id' => Auth::user()->id,
                        'total_rewards' => $totalRewards,
                        'month' => strtotime(date('Y-m', time()) . '-1'),
                        'type' => 1
                    ]);

                    createCardTrxHistory([
                        'falafel_card_id' => 0,
                        'action_type' => $totalRewards . ' Points Earned',
                        'transaction_amount' => $cartData['total_amount']
                    ]);

                    $user = Auth::user();

                    $user->membership_points = $user->membership_points + $totalRewards;

                    $user->save();
                }
            }

            (new CartDetail())
                ->leftJoin('cart_items', 'cart_items.cart_item_id', '=', 'cart_details.cart_item_id')
                ->leftJoin('cart_lists', 'cart_lists.cart_list_id', '=', 'cart_items.cart_list_id')
                ->where('cart_id', '=', $request->input('cart_id'))
                ->delete();

            (new CartItem())
                ->leftJoin('cart_lists', 'cart_lists.cart_list_id', '=', 'cart_items.cart_list_id')
                ->where('cart_id', '=', $request->input('cart_id'))
                ->delete();

            CartList::where('cart_id', '=', $request->input('cart_id'))->update([
                'total_tax' => 0,
                'order_total' => 0,
                'total_amount' => 0,
                'discount_amount' => 0,
                'coupon_id' => null,
                'bonus_id' => null
            ]);

            $this->validateAndUpdateMembership();

            $restaurant = Restaurant::where('id', '=', $request->input('restaurant_id'))->first();
            $lastOrder = $this->getSingleOrder($order->order_id);

            /*New Order alert to admin*/
            $notification_data = array(
                'title' => 'New Order Received',
                'order_id' => $lastOrder->original['response'][0]['order_id'],
                'ordered_by' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
                'user_id' => $lastOrder->original['response'][0]['user_id'],
                'message' => 'New order is received on ' . $restaurant['name'] . ' ' . $restaurant['address'],
                'pickup_time' => date('g:i a', $lastOrder->original['response'][0]['pickup_time']),
                'restaurant_id' => $restaurant['id'],
                'order_total' => "$" . number_format($cartData['total_amount'], 2),
            );

            $order_id = 'order_' . $lastOrder->original['response'][0]['order_id'];

            app('App\Http\Controllers\RealTimeController')->fireBase($notification_data, 'orders/' . $order_id, 1);

            //save data on firebase in order_list to manage realtime status
            $notification_data = array(
                'order_id' => $lastOrder->original['response'][0]['order_id'],
                'status' => 1
            );

            app('App\Http\Controllers\RealTimeController')->fireBase($notification_data, 'orders_list/' . $order_id, 1);
            /*Email Notification*/

            $template = view('email-templates.order-confirmation', [
                'name' => Auth::user()->first_name,
                'total_reward' => round($totalRewards),
                'pickup_time' => date('g:i a', $request->input('pickup_time')),
                'restaurant' => $restaurant['address'],
                'restaurant_contact' => $restaurant['contact_number'],
                'restaurant_id' => $restaurant['id'],
                'order_details' => $lastOrder->original['response'][0]
            ])->render();

            sendEmailFalafel($template, Auth::user()->email, 'Thank you for your order: Falafel Corner');

            $rewardPointsTemplate = view('email-templates.new-templates.reward-points',[
                'name' => Auth::user()->first_name,
                'total_reward_points' =>  $totalRewards,
            ]);
            sendEmailFalafel($rewardPointsTemplate, Auth::user()->email, "You have got ".$totalRewards." points : Falafel Corner");

            // ADMIN ORDER NOTIFICATION
            $adminTemplate = view('email-templates.admin-order', [
                'name' => Auth::user()->first_name,
                'total_reward' => round($totalRewards),
                'pickup_time' => date('g:i a', $request->input('pickup_time')),
                'restaurant' => $restaurant['address'],
                'restaurant_contact' => $restaurant['contact_number'],
                'restaurant_id' => $restaurant['id'],
                'order_details' => $lastOrder->original['response'][0],
                'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
                'user_email' => $lastOrder->original['response'][0]['user_email'],
                'user_number' => $lastOrder->original['response'][0]['user_number'],
                'order_id' => $lastOrder->original['response'][0]['order_id']
            ])->render();


            if ($restaurant['emails']) {
                $emails = explode(',', $restaurant['emails']);
                if(sizeof($emails)) {
                    foreach ($emails as $email) {
                        sendEmailFalafel($adminTemplate, $email, 'New Order #' . $lastOrder->original['response'][0]['order_id'] . ' on ' . $restaurant['name'], Auth::user()->email);
                    }
                }
            }
            /*Push Notification For Order confirmation*/
            $tokens = FirebaseToken::where('user_id', '=', Auth::user()->id)->get();
            // old notification message
            // 'Hi' . ' ' . Auth::user()->first_name . ', you\'ve earned' . ' ' . (int)$totalRewards . ' ' . 'reward points – nice! Click here to review your order details.'
            // End of old notification message
            $notification = array(
                'message' => 'Hi' . ' ' . Auth::user()->first_name . ', Order placed successfully! Click here to review your order details.',
                'title' => 'Order Placed',
                'body' => 'Hi' . ' ' . Auth::user()->first_name . ', Order placed successfully! Click here to review your order details.',
                'type' => 3,
                'data' => $lastOrder->original['response'][0],
                'sound' => 'default'
            );

            $notificationMessage = '';

            if ((int)$totalRewards != 0) {
                //$notificationMessage = 'Hi,' . ' ' . Auth::user()->first_name . ' you\'ve earned' . ' ' . (int)$totalRewards . ' ' . 'reward points – nice! Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . Auth::user()->first_name . ', Order placed successfully! Click here to review your order details.';
            }
            if ((int)$totalRewards == 0) {
                //$notificationMessage = 'Hi,' . ' ' . Auth::user()->first_name . 'Order placed successfully! Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . Auth::user()->first_name . ', Order placed successfully! Click here to review your order details.';
            }

            if ((int)$cartData['total_amount'] == 0) {
                //$notificationMessage = 'Hi,' . ' ' . Auth::user()->first_name . ' We congratulate you on using free reward on this order. Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . Auth::user()->first_name . ', Order placed successfully! Click here to review your order details.';
            }

            if(count($tokens)){
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notificationMessage, [$tk['token']], ['notification_type' => 1, 'order_id' => $lastOrder->original['response'][0]['order_id']]);
                }
            }

            if($request->input('preference') == 'delivery'){
                $deliveryController = new DeliveryController();
                $deliveryInfo = $deliveryController->generateDelivery(
                    $request->input('delivery_address'),
                    $request->input('delivery_notes'),
                    date('Y-m-d', $request->input('pickup_date')+25200),
                    date('H:i:s', $request->input('pickup_time')),
                    $selectedRestaurant,
                    $order
                );

                if($deliveryInfo){
                    Order::where('order_id', '=', $order->order_id)->update([
                        'postmates_delivery_id' => $deliveryInfo->id,
                        'postmates_tracking_url' => $deliveryInfo->tracking_url,
                        'delivery_status' => 'Pending'
                    ]);
                }
            }

            // SMS ORDER TO STAFF
            $contactNumber = $restaurant->contact_number;
            if($contactNumber){
                $contactNumber = removeSpecialChar($contactNumber);
                $contactNumber = '+1'.$contactNumber;
                $message = 'You have received a new order #'.$order->order_id. ' from Falafel Corner Website.';
                twillioSms($message, $contactNumber/*'+918962218285'*/);
                twillioCall($order->order_id,$contactNumber/*'+918962218285'*/);
            }

            return response()->json(apiResponseHandler($response, 'Order Confirmed', 200));
        } else {
            return response()->json(apiResponseHandler([], 'Sorry, restaurant is closed now.', 400), 400);
        }
    }

    public function validateAndUpdateMembership(){
        $user = Auth::user();
        $membershipPoints = $user->membership_points;

        // CURRENT MEMBERSHIP
        $userMembershipPlan = UserMembership::where('user_id',$user->id)->first();

        $userMembershipPlanId = $userMembershipPlan->membership_id;

        $basicMembership = Membership::where('membership_title', '=', 'Basic')->first();
        $silverMembership = Membership::where('membership_title', '=', 'Silver')->first();
        $goldMembership = Membership::where('membership_title', '=', 'Gold')->first();

        if(($userMembershipPlanId == $silverMembership->id || $userMembershipPlanId == $basicMembership->id) && $membershipPoints >= $goldMembership->membership_points_required){
            $userMembershipPlan->membership_id = $goldMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $goldMembership->membership_points_required;
            $user->save();
            UserRewardItems::where('user_id',$user->id)->delete();
        }elseif($userMembershipPlanId == $basicMembership->id && $membershipPoints >= $silverMembership->membership_points_required){
            $userMembershipPlan->membership_id = $silverMembership->id;
            $userMembershipPlan->membership_expiry = Carbon::today()->addDays(364)->format('Y-m-d');
            $userMembershipPlan->save();

            $user->membership_points = $user->membership_points - $silverMembership->membership_points_required;
            $user->save();
            UserRewardItems::where('user_id',$user->id)->delete();
        }
    }

    public function resetUserRewards(){
        $userMembership = UserMembership::where('user_id',Auth::user()->id)->first();
        $userRewardItems = UserRewardItems::where('user_id',Auth::user()->id)->count();

        if($userMembership->membership_id == 3 && $userRewardItems == 9){
            UserRewardItems::where('user_id',Auth::user()->id)->delete();
        }

        if($userMembership->membership_id != 3 && $userRewardItems == 7){
            UserRewardItems::where('user_id',Auth::user()->id)->delete();
        }
        return true;
    }

    public function applyBonus($isBonus, $cartId, $orderInfo)
    {
        if ($isBonus['bonus_type'] == 1) {
            UserRewards::create([
                'order_id' => 0,
                'user_id' => Auth::user()->id,
                'total_rewards' => $isBonus['bonus_extra_point'],
                'month' => strtotime(date('Y-m', time()) . '-1'),
                'type' => 1,
            ]);
        } else if ($isBonus['bonus_type'] == 2) {
            UserRewards::create([
                'order_id' => $orderInfo['order_id'],
                'user_id' => Auth::user()->id,
                'total_rewards' => $isBonus['bonus_points_multiplier'] * $orderInfo['total_rewards'],
                'month' => strtotime(date('Y-m', time()) . '-1'),
                'type' => 1,
            ]);
            BonusAppliedFor::where('user_id', '=', Auth::user()->id)->where('bonus_id', $isBonus['bonus_id'])->update(['is_used' => 1]);
        } else if ($isBonus['bonus_type'] == 3) {
//            $cartList = null;
//            $cartList = CartList::where('cart_id', '=', $cartId)->first();
//            $is_bonus_exist = BonusAppliedFor::where('user_id', '=', Auth::user()->id)->get();
//            if (count($is_bonus_exist)) {
//                CartItem::create([
//                    'item_id' => $isBonus['bonus_free_item_id'],
//                    'cart_list_id' => $cartList->cart_list_id,
//                    'receiver_name' => '',
//                    'item_flag' => 4,
//                    'bonus_id' => $isBonus['bonus_id']
//                ]);
//                $item = Item::where('item_id',$isBonus['bonus_free_item_id'])->first();
//                CartList::where('cart_id', '=', $cartId)->update([
//                    'discount_amount' => $item->item_price
//                ]);
//                $cartList = CartList::where('cart_id', '=', $cartId)->first();
//                BonusAppliedFor::where('user_id', '=', Auth::user()->id)->where('bonus_id',$isBonus['bonus_id'])->update(['is_used'=>1]);
//            } else {
//                return response()->json(apiResponseHandler([], 'No Bonus Item Found', 400), 400);
//            }
        } else if ($isBonus['bonus_type'] == 4) {
//            $myCart = CartList::where('cart_id', '=', $cartId)
//                ->first();
//            CartList::where('cart_list_id', '=', $myCart['cart_list_id'])->update([
//                'order_total' => $myCart['order_total'] - ($myCart['order_total'] * ($isBonus['bonus_discount'] / 100)),
//                'total_amount' => $myCart['total_tax'] + ($myCart['order_total'] - ($myCart['order_total'] * ($isBonus['bonus_discount'] / 100))),
//            ]);
            app('App\Http\Controllers\API\CartController')->applyBonus($isBonus['bonus_id']);
        }

        BonusAppliedFor::where('user_id', '=', Auth::user()->id)
            ->where('bonus_id', '=', $isBonus['bonus_id'])
            ->update(['is_used' => 1]);

        return response()->json(apiResponseHandler([], 'Bonus Applied Successfully.', 200), 200);
    }

    public function orderPayment($order, $selectedCard, $payment_data)
    {
        if ($payment_data) {
            $appleData = app('App\Http\Controllers\TokenDecryptController')->appleTokenDecrypt($payment_data);
            if ($appleData['status'] == 200) {
                $date = str_split($appleData['expiry']);
                $expMonth = $date[2] . $date[3];
                $expYear = $date[0] . $date[1];
                if ($order->restaurant_id == 1) {
                    $ordertrx = app('App\Http\Controllers\WalletPaymentController')->runCharge($appleData['account_number'], $expMonth, $expYear, $appleData['amount'] / 100, $appleData['cryptogram']);
                }
                if ($order->restaurant_id == 2) {
                    $ordertrx = app('App\Http\Controllers\WalletPaymentControllerMesa')->runCharge($appleData['account_number'], $expMonth, $expYear, $appleData['amount'] / 100, $appleData['cryptogram']);
                }
            } else {
                return ['ExpressResponseCode' => 103];
            }
        } else {
            $card = SavedCard::where('id', '=', $selectedCard)->first();

            if ($order->restaurant_id == 1) {
                $card = SavedCard::where('restaurant_id', '=', $order->restaurant_id)->where('card_group_id', $card['card_group_id'])->first();
                $ordertrx = app('App\Http\Controllers\PaymentProcessorController')->runCharge($card->token, $order->total_amount);
            }
            if ($order->restaurant_id == 2) {
                $card = SavedCard::where('restaurant_id', '=', $order->restaurant_id)->where('card_group_id', $card['card_group_id'])->first();
                $ordertrx = app('App\Http\Controllers\PaymentProcessorControllerMesa')->runCharge($card->token, $order->total_amount);
            }
        }
        if ($ordertrx['ExpressResponseCode'] == '0') {
            $orderPayment = new OrderPayment();
            $orderPayment->order_id = $order->order_id;
            $orderPayment->order_code = $ordertrx['Transaction']['TransactionID'];
            $orderPayment->token = isset($ordertrx['PaymentAccount']['PaymentAccountID']) ? $ordertrx['PaymentAccount']['PaymentAccountID'] : '';
            $orderPayment->order_description = '';
            $orderPayment->amount = $ordertrx['Transaction']['ApprovedAmount'];
            $orderPayment->currency_code = 'USD';
            $orderPayment->payment_status = $ordertrx['Transaction']['TransactionStatus'];
            $orderPayment->expiry_month = $ordertrx['Card']['ExpirationMonth'];
            $orderPayment->expiry_year = $ordertrx['Card']['ExpirationYear'];
            $orderPayment->card_type = $ordertrx['Card']['CardLogo'];
            $orderPayment->masked_card_number = $ordertrx['Card']['CardNumberMasked'];
            $orderPayment->reference_number = $ordertrx['Transaction']['ReferenceNumber'];
            $orderPayment->ticket_number = $ordertrx['Transaction']['ReferenceNumber'];
            $orderPayment->save();
        }
        return $ordertrx;
    }


    public function createCoupon($orderId)
    {
        $is_point = UserRewards::where('user_id', '=', Auth::user()->id)
            ->select('total_rewards', DB::raw('SUM(total_rewards) as total_point'))
            ->pluck('total_point');
        $lastOrder = $this->getSingleOrder($orderId);

        if ($is_point[0] >= 2000) {
            UserRewards::create([
                'order_id' => $orderId,
                'user_id' => Auth::user()->id,
                'total_rewards' => -2000,
                'month' => strtotime(date('Y-m', time()) . '-1'),
                'type' => 2
            ]);

            /*coupon creation*/
            RewardCoupon::create([
                'user_id' => Auth::user()->id,
                'expiry' => date(strtotime("+" . 6 . "Months")),
                'coupon_type' => 1,
            ]);

            /*Email Notification*/
            $template = view('email-templates.order-reward-points',
                [
                    'name' => Auth::user()->first_name,
                ])->render();

            $subscriptionPreference = SubscriptionPreference::where('user_id', '=', Auth::user()->id)->first();
            if ($subscriptionPreference['email_subscription']) {
                sendEmail($template, Auth::user()->email, 'Congratulations! Get your FREE Falafel Corner');
            }

            /*Push Notification*/
            $devicePreference = DevicePreference::where('user_id', '=', Auth::user()->id)->first();
            $tokens = FirebaseToken::where('user_id', '=', Auth::user()->id)->get();
            $receivers = FirebaseToken::where('user_id', '=', Auth::user()->id)->get();
            $notificationInfo = ManageNotifications::where('type_id', '=', 6)->first();

            $notification = array(
                'message' => $notificationInfo['message_text'],
                'title' => 'You got a free entree!',
                'body' => $notificationInfo['message_text'],
                'type' => $notificationInfo['type_id'],
                'data' => $lastOrder->original['response'][0],
                'sound' => 'default'
            );
            if ($devicePreference['push_notification']) {
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 6]);
                }
            }
            app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);
        }
    }

    public function getSingleOrder($orderId)
    {
        $response = Order::with(['orderDetails', 'transaction'])->where('orders.order_id', '=', $orderId)->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
            ->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->leftJoin('bonus', 'bonus.bonus_id', 'orders.bonus_id')
            ->where('orders.user_id', '=', Auth::user()->id)
            ->select(
                'orders.*',
                DB::raw('FORMAT(orders.total_tax,2) as total_tax'),
                DB::raw('FORMAT(orders.order_total,2) as order_total'),
                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
                'restaurants.name as restaurant_name',
                'restaurants.address as restaurant_address',
                'restaurants.contact_number as restaurant_contact_number',
                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
                'favorite_orders.favorite_order_id',
                'favorite_orders.favorite_label_id',
                'user_rewards.total_rewards',
                'reward_coupons.status as reward_coupons_status',
                'reward_coupons.coupon_type',
                'bonus.bonus_name'
            )
            ->get();

        if (sizeof($response)) {
            $response[0]['order_date'] = strtotime($response[0]['created_at']);
            foreach ($response[0]['orderDetails'] as $key => $value) {
                $value->order_item = OrderItem::where('order_detail_id', '=', $value->order_detail_id)
                    ->select(
                        'order_items.order_item_id',
                        'order_items.order_detail_id',
                        'order_items.modifier_group_id',
                        'order_items.item_count',
                        'order_items.item_name',
                        'order_items.item_price',
                        'order_items.item_image',
                        'order_items.item_description')
                    ->get();
            }
        }
        return response()->json(apiResponseHandler([$response[0]], 'success', 200));
    }

    public function getSingleOrderGuest($orderId)
    {
        $response = Order::with(['orderDetails', 'transaction'])->where('orders.order_id', '=', $orderId)->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
            ->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->select(
                'orders.*',
                DB::raw('FORMAT(orders.total_tax,2) as total_tax'),
                DB::raw('FORMAT(orders.order_total,2) as order_total'),
                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
                'restaurants.name as restaurant_name',
                'restaurants.address as restaurant_address',
                'restaurants.contact_number as restaurant_contact_number',
                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
                'favorite_orders.favorite_order_id',
                'favorite_orders.favorite_label_id',
                'user_rewards.total_rewards',
                'reward_coupons.status as reward_coupons_status',
                'reward_coupons.coupon_type'
            )
            ->get();

        if (sizeof($response)) {
            $response[0]['order_date'] = strtotime($response[0]['created_at']);
            $response[0]['pickup_date'] = strtotime($response[0]['pickup_date']);
            foreach ($response[0]['orderDetails'] as $key => $value) {
                $value->order_item = OrderItem::where('order_detail_id', '=', $value->order_detail_id)
                    ->select(
                        'order_items.order_item_id',
                        'order_items.order_detail_id',
                        'order_items.modifier_group_id',
                        'order_items.item_count',
                        'order_items.item_name',
                        'order_items.item_price',
                        'order_items.item_image',
                        'order_items.item_description')
                    ->get();
            }
        }

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function orderHistory()
    {
        $response = Order::with(['orderDetails'])
            ->where('orders.user_id', '=', Auth::user()->id)
            ->select('*',DB::raw('UNIX_TIMESTAMP(created_at) as order_date'))
            ->orderBy('orders.order_id', 'DESC')
            ->get();

//        $response['past'] = Order::with([
//            'orderDetails', 'feedback'])
//            ->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
//            ->leftJoin('menus', 'menus.menu_id', 'orders.menu_id')
//            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
//            ->leftJoin('favorite_labels', 'favorite_labels.favorite_label_id', 'favorite_orders.favorite_label_id')
//            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
//            ->where('orders.user_id', '=', Auth::user()->id)
//            ->where('orders.status', '=', 3)
//            ->orderBy('orders.order_id', 'DESC')
//            ->groupBy('orders.order_id')
//            ->select('orders.order_id',
//                'orders.reference_id',
//                'orders.menu_id',
//                'restaurants.name as restaurant_name',
//                'restaurants.address as restaurant_address',
//                'menus.menu_name',
//                'orders.pickup_time',
//                'orders.preparation_time',
//                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
//                'orders.status',
//                'orders.created_at',
//                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
//                'favorite_orders.favorite_order_id',
//                'favorite_orders.favorite_label_id',
//                'favorite_labels.label_name',
//                'user_rewards.total_rewards')
//            ->get();

//        $response['favorite'] = Order::with([
//            'orderDetails', 'feedback'])
//            ->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
//            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
//            ->leftJoin('favorite_labels', 'favorite_labels.favorite_label_id', 'favorite_orders.favorite_label_id')
//            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
//            ->where('orders.user_id', '=', Auth::user()->id)
//            ->whereNotNull('favorite_orders.favorite_order_id')
//            ->orderBy('orders.order_id', 'DESC')
//            ->groupBy('orders.order_id')
//            ->select('orders.order_id',
//                'orders.reference_id',
//                'orders.menu_id',
//                'restaurants.name as restaurant_name',
//                'restaurants.address as restaurant_address',
//                'orders.pickup_time',
//                'orders.preparation_time',
//                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
//                'orders.status',
//                'orders.created_at',
//                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
//                'favorite_orders.favorite_order_id',
//                'favorite_orders.favorite_label_id',
//                'favorite_labels.label_name',
//                'user_rewards.total_rewards')
//            ->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function orderHistoryDetails($response)
    {
        foreach ($response as $item) {
            $item->order_date = strtotime($item->created_at);
            foreach ($item->orderDetails as $key => $value) {
                $value->order_item = OrderItem::where('order_detail_id', '=', $value->order_detail_id)
                    ->select(
                        'order_items.order_item_id',
                        'order_items.order_detail_id',
                        'order_items.item_count',
                        'order_items.item_name',
                        'order_items.item_price',
                        'order_items.item_image',
                        'order_items.item_description')
                    ->get();
            }
        };
        return $response;
    }

    public function getActiveOrders()
    {
        $response = Order::with([
            'orderDetails', 'feedback'])
            ->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
            ->leftJoin('favorite_labels', 'favorite_labels.favorite_label_id', 'favorite_orders.favorite_label_id')
            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
            ->where('orders.user_id', '=', Auth::user()->id)
            ->whereIn('orders.status', [1, 2])
            ->orderBy('orders.order_id', 'DESC')
            ->groupBy('orders.order_id')
            ->select('orders.order_id',
                'orders.reference_id',
                'restaurants.name as restaurant_name',
                'restaurants.address as restaurant_address',
                'orders.pickup_time',
                'orders.preparation_time',
                'orders.total_amount',
                'orders.status',
                'orders.created_at',
                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
                'favorite_orders.favorite_order_id',
                'favorite_orders.favorite_label_id',
                'favorite_labels.label_name',
                'user_rewards.total_rewards')
            ->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function FavoriteLabels()
    {
        $response = FavoriteLabel::where(function ($query) {
            $query->where('added_by', '=', 0)
                ->orWhere('added_by', '=', Auth::user()->id);
        })->get();
        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function markAsFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'favorite_label_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $isAlready = FavoriteOrder::where('user_id', '=', Auth::user()->id)
            ->where('order_id', '=', $request->input('order_id'))->get();

        if (count($isAlready)) {
            FavoriteOrder::where('user_id', '=', Auth::user()->id)
                ->where('order_id', '=', $request->input('order_id'))->delete();
            return response()->json(apiResponseHandler([], 'Removed from favorite', 200), 200);
        } else {
            $favoriteOrder = new FavoriteOrder();
            $favoriteOrder->order_id = $request->input('order_id');
            $favoriteOrder->user_id = Auth::user()->id;
            $favoriteOrder->favorite_label_id = $request->input('favorite_label_id');
            $favoriteOrder->save();
            return response()->json(apiResponseHandler([], 'Marked as favorite', 200), 200);
        }
    }

    public function myRewards()
    {
        $response['total_rewards'] = 0;
        $response['list'] = UserRewards::with(['rewards' => function ($query) {
            $query->where('user_id', '=', Auth::user()->id)
                ->orderBy('reward_id', 'DESC')
                ->select(
                    'reward_id',
                    'type',
                    'order_id',
                    'user_id',
                    'total_rewards',
                    'month',
                    'created_at',
                    DB::raw('UNIX_TIMESTAMP(created_at) as order_date'));
        }])
            ->where('user_id', '=', Auth::user()->id)
            ->groupBy('month')
            ->orderBy('created_at', 'DESC')
            ->select('month', DB::raw('DATE_FORMAT(FROM_UNIXTIME(month), "%M %Y") as month_name'))
            ->get();

        $response['total_rewards'] = UserRewards::where('user_id', '=', Auth::user()->id)
            ->select(DB::raw('SUM(total_rewards) as points'))
            ->first();

        $response['total_rewards']['points'] = $response['total_rewards']['points'] ? $response['total_rewards']['points'] : '0';
        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function getRewardItems($flag)
    {
        $response = [];
        if ($flag == 1) {
            /*get point reward items*/
            $is_point = RewardCoupon::where('user_id', '=', Auth::user()->id)
                ->where('coupon_type', '=', 1)
                ->get();
            if (count($is_point)) {
                $response = RewardsItem::with(['item'])->where('is_enable', '=', 1)
                    ->where('flag', '=', 1)
                    ->get();
            }
        } elseif ($flag == 2) {
            /*get birthday reward items*/
            $is_point = RewardCoupon::where('user_id', '=', Auth::user()->id)
                ->where('coupon_type', '=', 2)
                ->get();
            if (count($is_point)) {
                $response = RewardsItem::with(['item'])->where('is_enable', '=', 1)
                    ->where('flag', '=', 2)
                    ->get();
                return response()->json(apiResponseHandler($response, 'success', 200));
            }
        } elseif ($flag == 3) {
            /*get admin reward items*/
            $is_reward = AssignAdminReward::where('user_id', '=', Auth::user()->id)->get();
            if (count($is_reward)) {
                $response = AssignAdminReward::where('user_id', '=', Auth::user()->id)
                    ->leftJoin('admin_rewards', 'admin_rewards.admin_reward_id', 'assign_admin_rewards.admin_reward_id')
                    ->get();
                foreach ($response as $value) {
                    if ($value['item_id']) {
                        $value['item'] = AdminReward::where('unique_key', '=', $value['unique_key'])
                            ->leftJoin('items', 'items.item_id', 'admin_rewards.item_id')
                            ->first();
                        /* $value['item'] = $value['item']->toArray();*/
                    }
                }
            }
        }

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function getCoupon()
    {
        /*get point rewards*/
        $response['order_rewards_coupons'] = RewardCoupon::where('user_id', '=', Auth::user()->id)
            ->where('coupon_type', '=', 1)
            ->where('status', '=', 1)
            ->select('coupon_id', 'user_id', 'expiry', 'status', 'coupon_type', 'created_at', DB::raw('UNIX_TIMESTAMP(created_at) as order_date'))
            ->orderBy('coupon_id', 'DESC')
            ->get();

        /*get birthday coupon*/
        $response['birthday_coupons'] = [];
        $is_user = User::where('id', '=', Auth::user()->id)
            ->select('date_of_birth', DB::raw('DATE_FORMAT(FROM_UNIXTIME(date_of_birth), "%d-%m") as dob'))
            ->get();

        $today = date('d-m');
        if (count($is_user)) {
            if ($today == $is_user[0]['dob']) {
                $response['birthday_coupons'] = RewardCoupon::where('user_id', '=', Auth::user()->id)
                    ->where('coupon_type', '=', 2)
                    ->select('coupon_id', 'user_id', 'expiry', 'status', 'created_at', DB::raw('UNIX_TIMESTAMP(created_at) as order_date'))
                    ->get();
            }
        }

        /*get admin Reward*/
        $response['admin_reward_coupon'] = [];
        $is_reward = AssignAdminReward::where('user_id', '=', Auth::user()->id)->get();
        if (count($is_reward)) {
            $response['admin_reward_coupon'] = AssignAdminReward::where('assign_admin_rewards.user_id', '=', Auth::user()->id)
                ->leftJoin('admin_rewards', 'admin_rewards.admin_reward_id', 'assign_admin_rewards.admin_reward_id')
                ->leftJoin('reward_coupons', 'reward_coupons.admin_reward_id', 'assign_admin_rewards.admin_reward_id')
                ->select('assign_admin_rewards.id',
                    'assign_admin_rewards.admin_reward_id',
                    'admin_rewards.name',
                    'admin_rewards.description',
                    'reward_coupons.status',
                    'reward_coupons.coupon_id',
                    'admin_rewards.reward_point',
                    'admin_rewards.item_id',
                    'admin_rewards.expiry')
                ->orderBy('id', 'DESC')
                ->get();
        }

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function getBirthdayBonus()
    {
        $is_user = User::where('id', '=', Auth::user()->id)
            ->select('date_of_birth', DB::raw('DATE_FORMAT(FROM_UNIXTIME(date_of_birth), "%d-%m-%Y") as date_of_birth'))
            ->get();

        $today = date('d-m-Y');
        if (count($is_user)) {
            if ($today == $is_user[0]['date_of_birth']) {
                $response = RewardsItem::with(['item'])->where('is_enable', '=', 1)
                    ->where('flag', '=', 2)
                    ->get();
                return response()->json(apiResponseHandler($response, 'success', 200));
            }
        }
    }

    public function testNotification()
    {
        $tokens = ['deYU4CwHrf8:APA91bHq0hvUrPCb6pw9_lnAd8Guus2GNV-gAiHP4cu8OZ9SPVJuUlAJr_zg8Pc9DIyYoQgjvSehyMrJer-6VEz0wU7OGEbclfbv4lSfBRHqgFwxOV-JhHc8Q_KyTR6lIZ25zDEy_lqr'];
        $notification = array(
            'message' => 'Hello ! We would love to know your experience with last order.',
            'title' => 'Order Completed',
            'body' => 'Hello ! We would love to know your experience with last order.',
            'type' => 4,
            'data' => (object)array(),
            'sound' => 'default'
        );
        foreach ($tokens as $tk) {
            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']]);
        }
    }

    public function customerFavorites()
    {
        $response = OrderDetail::with(['item'])->select('order_details.item_id', DB::raw('COUNT(item_id) as top_count'))
            ->groupBy('item_id')
            ->orderBy('top_count', 'DESC')
            ->take(10)
            ->get();
        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    /*Deprecated*/
    public function checkCard($selectedCard, $cvc)
    {
        $card = SavedCard::where('id', '=', $selectedCard)->first();

        $postData = array(
            'clientKey' => config('worldpay.sandbox.client'),
            'cvc' => $cvc
        );

        $postData = json_encode($postData);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.worldpay.com/v1/tokens/$card->token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            )
        ));

        $response = curl_exec($curl);

        $response = json_decode($response, true);

        if (isset($response['httpStatusCode'])) {
            return apiResponseHandler([], $response['message'], 400);
        }

        return apiResponseHandler([], 'success', 200);
    }

}
