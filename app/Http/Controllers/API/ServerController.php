<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Admin\DeliveryController;
use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\Cart\CartDetail;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\FirebaseToken;
use App\Models\Item;
use App\Models\Menu;
use App\Models\ModifierGroup;
use App\Models\ModifierItems;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\OrderItem;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use App\Models\User\UserCard;
use App\Models\User\UserRewardItems;
use App\Models\UserRewards;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class ServerController extends Controller
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

            $orderTotal = 0;
            $totalTax = 0;
            $cartItems = null;
            $cartDetails = null;
            $singleModifier = null;
            $bonusType = 0;

            $cartItems = CartItem::where('cart_list_id', '=', $cartList[0]['cart_list_id'])->get();


            $cartData = cartCalculation($cartList[0]['cart_list_id']);

            $selectedMenu = Menu::where('restaurant_id', '=', $request->input('restaurant_id'))
                ->where('menu_id', '=', $cartData['menu_id'])
                ->first();

            $agent = new Agent();
            $orderDevice = 2;

            $cardInfo = UserCard::find($request->input('selected_card'));

            if (!$cardInfo) {
                return response()->json(apiResponseHandler([], 'Invalid card.', 400), 400);
            }

            $orderUser = User::find($cardInfo->user_id);

            if (!$orderUser) {
                return response()->json(apiResponseHandler([], 'Invalid card user information.', 400), 400);
            }

            $order = new Order();
            $order->reference_id = $selectedMenu['reference_id_text'] . '-' . generateRefId();/*$order->reference_id = date('Y') . time()*/;
            $order->user_id = $orderUser->id;
            $order->restaurant_id = $request->input('restaurant_id');
            $order->pickup_time = $request->input('pickup_time');
            $order->pickup_date = date('Y-m-d', $request->input('pickup_date') + 25200);
            $order->preparation_time = $request->input('pickup_time') - $selectedRestaurant['preparation_time'];
            $order->order_total = $cartData['order_total'];
            $order->total_tax = $cartData['total_tax'];
            $order->total_amount = $cartData['total_amount'];
            $order->discount_amount = $cartData['discount_amount'];
            $order->delivery_fee = $cartData['delivery_fee'];
            $order->coupon_id = $cartData['coupon_id'];
            $order->menu_id = $cartData['menu_id'];
            $order->user_first_name = $orderUser->first_name;
            $order->user_last_name = $orderUser->last_name;
            $order->user_email = $orderUser->email;
            $order->user_number = $orderUser->mobile;
            $order->order_type = $request->input('preference') == 'pickup' ? 1 : 2;
            $order->bonus_id = $request->input('bonus_id') ? $request->input('bonus_id') : 0;
            $order->order_device = $orderDevice;
            $order->is_server_order = 1;
            $order->server_user_id = Auth::user()->id;
            $order->save();

            $pointMultiply = 1;
            if ($cartList[0]['total_amount'] > 0) {
                if ($request->input('selected_card') != '') {
                    $walletController = new WalletController();

                    $validateCardBalance = $walletController->validateCardBalance($request->input('selected_card'), $cartList[0]['total_amount']);

                    if ($validateCardBalance) {
                        $result = $walletController->makeOrderPayment($request->input('selected_card'), $cartList[0]['total_amount'], $order->order_id);
                        $pointMultiply = 2;
                        if (!$result) {
                            Order::where('order_id', '=', $order->order_id)->delete();
                            return response()->json(apiResponseHandler([], 'Something went wrong. Please try again.', 400), 400);
                        }
                    } else {
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

            $totalRewards = 0;
            if ($cartData['total_amount'] > 0) {
                $totalRewards = round($cartData['order_total'], 2);
            }
            $response['order_id'] = $order->order_id;
            $response['total_rewards'] = $totalRewards;

            if ($totalRewards > 0) {
                $totalRewards = $totalRewards * $pointMultiply;
                UserRewards::create([
                    'order_id' => $order->order_id,
                    'user_id' => $orderUser->id,
                    'total_rewards' => $totalRewards,
                    'month' => strtotime(date('Y-m', time()) . '-1'),
                    'type' => 1
                ]);

                createCardTrxHistoryServer([
                    'falafel_card_id' => 0,
                    'action_type' => $totalRewards . ' Points Earned',
                    'transaction_amount' => $cartData['total_amount']
                ], $orderUser->id);

                $user = User::find($orderUser->id);

                $user->membership_points = $user->membership_points + $totalRewards;

                $user->save();
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

            $restaurant = Restaurant::where('id', '=', $request->input('restaurant_id'))->first();
            $lastOrder = $this->getSingleOrder($order->order_id, $orderUser->id);

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
                'name' => $orderUser->first_name,
                'total_reward' => round($totalRewards),
                'pickup_time' => date('g:i a', $request->input('pickup_time')),
                'restaurant' => $restaurant['address'],
                'restaurant_contact' => $restaurant['contact_number'],
                'restaurant_id' => $restaurant['id'],
                'order_details' => $lastOrder->original['response'][0]
            ])->render();

            sendEmailFalafel($template, $orderUser->email, 'Thank you for your order: Falafel Corner');

            // ADMIN ORDER NOTIFICATION
            $adminTemplate = view('email-templates.admin-order', [
                'name' => $orderUser->first_name,
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
                foreach ($emails as $email) {
                    sendEmail($adminTemplate, $email, 'New Order #' . $lastOrder->original['response'][0]['order_id'] . ' on ' . $restaurant['name'], $orderUser->email);
                }
            }
            /*Push Notification For Order confirmation*/
            $tokens = FirebaseToken::where('user_id', '=', $orderUser->id)->get();
            $receivers = FirebaseToken::where('user_id', '=', $orderUser->id)->get();
            // Old notification message
            // 'Hi' . ' ' . $orderUser->first_name . ', you\'ve earned' . ' ' . (int)$totalRewards . ' ' . 'reward points – nice! Click here to review your order details.',
            // End of old notification message
            $notification = array(
                'message' => 'Hi' . ' ' . $orderUser->first_name . ', Order placed successfully! Click here to review your order details.',
                'title' => 'Order Placed',
                'body' => 'Hi' . ' ' . $orderUser->first_name . ', Order placed successfully! Click here to review your order details.',
                'type' => 3,
                'data' => $lastOrder->original['response'][0],
                'sound' => 'default'
            );

            $notificationMessage = '';

            if ((int)$totalRewards != 0) {
                //$notificationMessage = 'Hi,' . ' ' . $orderUser->first_name . ' you\'ve earned' . ' ' . (int)$totalRewards . ' ' . 'reward points – nice! Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . $orderUser->first_name . ', Order placed successfully! Click here to review your order details.';
            }
            if ((int)$totalRewards == 0) {
                //$notificationMessage = 'Hi,' . ' ' . $orderUser->first_name . 'Order placed successfully! Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . $orderUser->first_name . ', Order placed successfully! Click here to review your order details.';
            }

            if ((int)$cartData['total_amount'] == 0) {
                //$notificationMessage = 'Hi,' . ' ' . $orderUser->first_name . ' We congratulate you on using free reward on this order. Click here to review your order details.';
                $notificationMessage = 'Hi' . ' ' . $orderUser->first_name . ', Order placed successfully! Click here to review your order details.';
            }

            foreach ($tokens as $tk) {
                app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notificationMessage, [$tk['token']], ['notification_type' => 1, 'order_id' => $lastOrder->original['response'][0]['order_id']]);
            }

            return response()->json(apiResponseHandler($response, 'Order Confirmed', 200));
        } else {
            return response()->json(apiResponseHandler([], 'Sorry, restaurant is closed now.', 400), 400);
        }
    }

    public function getSingleOrder($orderId, $userId)
    {
        $response = Order::with(['orderDetails', 'transaction'])->where('orders.order_id', '=', $orderId)->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
            ->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
            ->leftJoin('user_rewards', 'user_rewards.order_id', 'orders.order_id')
            ->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->leftJoin('bonus', 'bonus.bonus_id', 'orders.bonus_id')
            ->where('orders.user_id', '=', $userId)
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

    public function getServerUserOrders(Request $request)
    {
        $limit = 10;
        $offset = 0;
        if ($request->query('page')) {
            $page = $request->query('page') - 1;
            $limit = 10;
            $offset = $page * $limit;
        }
        $response['orders'] = Order::with(['orderDetails'])
            ->where('orders.server_user_id', '=', Auth::user()->id)
            ->select('*', DB::raw('UNIX_TIMESTAMP(created_at) as order_date'))
            ->orderBy('orders.order_id', 'DESC')
            ->offset($offset)->limit($limit)->get();
        $response['total'] = Order::where('orders.server_user_id', '=', Auth::user()->id)->count();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function addUserPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'points' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }
        $userId = $request->input('user_id');
        $totalRewards = $request->input('points');
        $orderUser = User::find($userId);

        if (!$orderUser) {
            return response()->json(apiResponseHandler([], 'Invalid user information.', 400), 400);
        }
        if ($totalRewards > 0) {
            $order = new Order();
            $order->reference_id = generateRefId();/*$order->reference_id = date('Y') . time()*/;
            $order->user_id = $orderUser->id;
            $order->restaurant_id = Auth::user()->assigned_restaurant;
            $order->pickup_time = time();
            $order->pickup_date = date('Y-m-d', time() + 25200);
            $order->preparation_time = time();
            $order->order_total = 0;
            $order->total_tax = 0;
            $order->total_amount = 0;
            $order->discount_amount = 0;
            $order->delivery_fee = 0;
            $order->coupon_id = 0;
            $order->menu_id = 0;
            $order->user_first_name = $orderUser->first_name;
            $order->user_last_name = $orderUser->last_name;
            $order->user_email = $orderUser->email;
            $order->user_number = $orderUser->mobile;
            $order->order_type = 1;
            $order->bonus_id = 0;
            $order->order_device = 1;
            $order->is_server_order = 1;
            $order->server_user_id = Auth::user()->id;
            $order->save();

            UserRewards::create([
                'order_id' => $order->order_id,
                'user_id' => $orderUser->id,
                'total_rewards' => $totalRewards,
                'month' => strtotime(date('Y-m', time()) . '-1'),
                'type' => 1
            ]);

            createCardTrxHistoryServer([
                'falafel_card_id' => 0,
                'action_type' => $totalRewards . ' Points Earned',
                'transaction_amount' => 0
            ], $orderUser->id);

            $user = User::find($orderUser->id);

            $user->membership_points = $user->membership_points + $totalRewards;

            $user->save();

            $tokens = FirebaseToken::where('user_id', '=', $orderUser->id)->get();

            $notification = array(
                'message' => 'Hi' . ' ' . $orderUser->first_name . ', '.$totalRewards.' Points has been credited to your account.',
                'title' => 'Manual Point Credit',
                'body' => 'Hi' . ' ' . $orderUser->first_name . ', '.$totalRewards.' Points has been credited to your account.',
                'type' => 25,
                'data' => [],
                'sound' => 'default'
            );

            if(count($tokens)){
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['message'], [$tk['token']], ['notification_type' => 25]);
                }
            }

            return response()->json(apiResponseHandler([], 'Points added to user profile.', 200), 200);
        }else{
            return response()->json(apiResponseHandler([], 'error', 400), 400);
        }
    }

    public function verifyCard(Request $request){
        $validator = Validator::make($request->all(), [
            'card_number' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $validateCard = UserCard::where('card_number', $request->input('card_number'))
                        ->first();
        if($validateCard){
            if($validateCard->balance < $request->input('amount')){
                return response()->json(apiResponseHandler([], 'Insufficient balance in card to place this order.', 400), 400);
            }
            $data = [
                'card_id'=>$validateCard->id,
                'amount' =>$validateCard->balance
            ];
            return response()->json(apiResponseHandler($data, '', 200), 200);
        }else{
            return response()->json(apiResponseHandler([], 'Invalid card.', 400), 400);
        }
    }

    public function getUserInfo($userId){
        $user = User::find($userId);

        $response['user_name'] = $user->first_name . ' ' . $user->last_name;
        $points = UserRewards::where('user_id', '=', $userId)
            ->select(DB::raw('SUM(total_rewards) as points'))
            ->first();
        $response['total_points'] = $points->points ? $points->points : "0";

        return response()->json(apiResponseHandler($response, '', 200), 200);
    }
}
