<?php

namespace App\Http\Controllers\API;

use Alvee\WorldPay\lib\Worldpay;
use Alvee\WorldPay\lib\WorldpayException;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Controller;
use App\Models\Cart\CartDetail;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\DevicePreference;
use App\Models\GuestCustomer;
use App\Models\Item;
use App\Models\Menu;
use App\Models\ModifierGroup;
use App\Models\ModifierItems;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\OrderItem;
use App\Models\OrderPayment;
use App\Models\Restaurant;
use App\Models\SavedCard;
use App\Models\SubscriptionPreference;
use App\Models\UserRewards;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function getCards(Request $request)
    {
        $cards = (new SavedCard())->where('user_id', '=', Auth::user()->id)->get();

        return response()->json(apiResponseHandler($cards, 'Success'));
    }

    public function deleteCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')->where('user_id', Auth::user()->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $card = SavedCard::where('id', '=', $request->input('card_id'))->first();

        SavedCard::where('card_group_id', '=', $card['card_group_id'])->delete();

        return response()->json(apiResponseHandler([], 'Success'));
    }

    public function makeOrderPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')->where('user_id', Auth::user()->id)
            ],
            'cvc' => 'required|digits_between:3,4'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $card = SavedCard::where('id', '=', $request->input('card_id'))->first();

        $postData = array(
            'clientKey' => config('worldpay.sandbox.client'),
            'cvc' => $request->input('cvc')
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
            return response(apiResponseHandler([], $response['message'], 400), 400);
        }

        $total = 5;

        $worldPay = new Worldpay(config('worldpay.sandbox.service'));

        try {
            $response = $worldPay->createOrder(array(
                'token' => $card->token,
                'amount' => (float)($total) * 100,
                'currencyCode' => 'USD',
                'name' => $card->name,
                'orderDescription' => 'Order description',
                'customerOrderCode' => uniqid()
            ));

            if ($response['paymentStatus'] === 'SUCCESS') {
                $payment = [];

                foreach ($response as $key => $value) {
                    if (in_array($key, ['paymentResponse', 'resultCodes'])) {
                        foreach ($response[$key] as $k => $v) {
                            $column = fromCamelCase($k);
                            if (Schema::hasColumn('order_payments', $column)) {
                                $payment[$column] = $v;
                            }
                        }
                    } else {
                        $column = fromCamelCase($key);
                        if (Schema::hasColumn('order_payments', $column)) {
                            $payment[$column] = $value;
                        }
                    }
                }

                dd($payment);
            } else {
                throw new WorldpayException(print_r($response, true));
            }
        } catch (WorldpayException $e) {
            return response(apiResponseHandler([], $e->getMessage(), $e->getHttpStatusCode()), 400);
        } catch (Exception $e) {
            return response(apiResponseHandler([], $e->getMessage(), 400), 400);
        }
    }

    public function guestCheckout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => [
                'required',
                Rule::exists('cart_lists', 'cart_id')
            ],
            'restaurant_id' => 'required',
            'pickup_date' => 'required',
            'pickup_time' => 'required',
            'user_first_name' => 'required',
            'user_last_name' => 'required',
            'user_email' => 'required',
            'user_number' => 'required',
            'preference' => 'required',
            'nonce' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $_nonce = $request->input('nonce');

        if(!isset($_nonce['token']['id'])){
            return response()->json(apiResponseHandler([], 'Payment information is missing.', 400), 400);
        }

        $cartList = CartList::where('cart_id', '=', $request->input('cart_id'))->get();
        $orderTotal = 0;
        $totalTax = 0;
        $cartItems = null;
        $cartDetails = null;
        $singleModifier = null;
        $selectedRestaurant = Restaurant::where('id', '=', $request->input('restaurant_id'))->first();
        $cartData = cartCalculation($cartList[0]['cart_list_id']);
        $selectedMenu = Menu::where('restaurant_id', '=', $request->input('restaurant_id'))
            ->where('menu_id', '=', $cartData['menu_id'])
            ->first();
        $agent = new Agent();
        $orderDevice = 1;
        if ($agent->isMobile()) {
            $orderDevice = 2;
        }

        $order = new Order();
        $order->reference_id = $selectedMenu['reference_id_text'] . '-' . generateRefId();/*$order->reference_id = date('Y') . time()*/;
        $order->user_id = 0;
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
        $order->user_first_name = $request->input('user_first_name');
        $order->user_last_name = $request->input('user_last_name');
        $order->user_email = $request->input('user_email');
        $order->user_number = $request->input('user_number');
        $order->order_type = $request->input('preference') == 'pickup' ? 1 : 2;
        $order->order_device = $orderDevice;
        if ($request->has('firebase_token') && $request->has('firebase_token') != '') {
            $order->firebase_token = $request->input('firebase_token');
        }
        if($request->input('preference') === 'delivery'){
            $order->delivery_address = $request->input('delivery_address');
            $order->delivery_notes = $request->input('delivery_notes');
        }
        $order->save();

        $totalRewards = $cartData['total_amount'] * 10;
        $data['order_id'] = $order->order_id;
        $data['total_rewards'] = $totalRewards;



        //save guest customer details
        GuestCustomer::updateOrCreate(
            ['guest_email' => $request->input('user_email')],
            ['guest_first_name' => $request->input('user_first_name'), 'guest_last_name' => $request->input('user_last_name'), 'guest_email' => $request->input('user_email'), 'guest_mobile' => $request->input('user_number')]
        );

        //$result = app('App\Http\Controllers\API\StripePaymentController')->paymentWithToken($cartData['total_amount'],$request->input('nonce'),$request->input('restaurant_id'));
        //$result = app('App\Http\Controllers\API\StripePaymentController')->paymentWithCardId($cartData['total_amount'],$request->input('payment_data'),$request->input('restaurant_id'));

        /*\Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        $_nonce = '';

        if ($request->input('payment_data')) {
            $payData = $request->input('payment_data');
            $_nonce = $payData['token'];
        }else{
            $_nonce = $request->input('nonce');
        }

        $merchantAccountId = 'qualwebs';

        $result = \Braintree\Transaction::sale([
            'amount' => (float)($cartData['total_amount']),
            'paymentMethodNonce' => $_nonce,
            'options' => [
                'submitForSettlement' => True,
            ],
            'merchantAccountId' => $merchantAccountId
        ]);*/


        /*if ($result->success) {*/


        $stripeController = new StripePaymentController();

        $resBank = Restaurant::with('bankAccount')->where('id',$selectedRestaurant->id)->first();
        $applicationfee = $resBank->commission;
        if($resBank->commission_type == 2){
            $applicationfee = ($cartData['order_total'] * $applicationfee)/100;
        }
        try{
            $apiKey = $stripeController->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $result = Charge::create([
                'amount' => $cartData['total_amount'] * 100,
                'currency' => 'USD',
                'source' => $_nonce['token']['id'],
                'destination' => $resBank->bankAccount->bank_account_id,
                'application_fee' => $applicationfee * 100
            ]);
        }catch (ApiErrorException $e){
            return response(apiResponseHandler([], $e->getMessage(), 400), 400);
        }

        if($result->status == "succeeded"){
            $orderPayment = new OrderPayment();
            $orderPayment->order_id = $order->order_id;
            $orderPayment->order_code = $result->id;
            $orderPayment->token = $result->source->id;
            $orderPayment->order_description = '';
            $orderPayment->amount = $cartData['total_amount'];
            $orderPayment->currency_code = 'USD';
            $orderPayment->payment_status = 1;
            $orderPayment->expiry_month = $result->payment_method_details->card->exp_month;
            $orderPayment->expiry_year = $result->payment_method_details->card->exp_year;
            $orderPayment->card_type = $result->payment_method_details->card->network;
            $orderPayment->masked_card_number = $result->payment_method_details->card->last4;
            $orderPayment->save();
        } else {
            Order::where('order_id', '=', $order->order_id)->delete();
            return response(apiResponseHandler([], 'Something went wrong, Try again.', 400), 400);
        }

        Order::where('order_id', '=', $order->order_id)->update(['payment_status' => 1]);
        /*Cart Items*/
        $cartItems = CartItem::where('cart_list_id', '=', $cartList[0]['cart_list_id'])->get();

        if (count($cartItems)) {
            foreach ($cartItems as $item) {
                $menuPrice = Item::where('item_id', '=', $item['item_id'])->first();
                $orderDetails = new OrderDetail();
                $orderDetails->order_id = $order->order_id;
                $orderDetails->item_id = $item['item_id'];
                $orderDetails->item_price = $menuPrice['item_price'];
                $orderDetails->item_count = $item['item_count'];
                $orderDetails->item_name = $menuPrice['item_name'];
                $orderDetails->item_image = $menuPrice['item_image'];
                $orderDetails->item_description = $menuPrice['item_description'];
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
                            $orderModifier->item_id = $itemPrice['item_id'];
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
            'coupon_id' => null
        ]);

        $lastOrder = $this->getSingleOrder($order->order_id);
        /*PUSH Notification*/
        if ($lastOrder->original['response'][0]['firebase_token']) {
            $notificationMessage = 'Hi,' . ' ' . $lastOrder->original['response'][0]['user_first_name'] . 'Order placed successfully! Click here to review your order details.';
            app('App\Http\Controllers\RealTimeController')->cloudNotifications('Order Placed', $notificationMessage, [$lastOrder->original['response'][0]['firebase_token']], ['notification_type' => 1, 'order_id' => $lastOrder->original['response'][0]['order_id']]);
        }
        /*Email Notification*/
        $restaurant = Restaurant::where('id', '=', $request->input('restaurant_id'))->first();

        $template = view('email-templates.guest-checkout', [
            'name' => $order->user_first_name,
            'pickup_time' => date('g:i a', $request->input('pickup_time')),
            'restaurant' => $restaurant['address'],
            'restaurant_contact' => $restaurant['contact_number'],
            'restaurant_id' => $restaurant['id'],
            'order_details' => $lastOrder->original['response'][0]
        ])->render();

        sendEmailFalafel($template, $order->user_email, 'Thank you for your order: Falafel Corner');

        // ADMIN ORDER NOTIFICATION
        $adminTemplate = view('email-templates.admin-order', [
            'name' => $order->user_first_name,
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
            if(sizeof($emails)){
                foreach ($emails as $email) {
                    sendEmailFalafel($adminTemplate, $email, 'New Order #' . $lastOrder->original['response'][0]['order_id'] . ' on ' . $restaurant['name']);
                }
            }
        }

        $notification_data = array(
            'title' => 'New Order Received',
            'order_id' => $lastOrder->original['response'][0]['order_id'],
            'message' => 'New order is received on ' . $restaurant['name'] . ' ' . $restaurant['address'],
            'restaurant_id' => $restaurant['id'],
            'order_total' => round($cartData['total_amount'], 2),
        );


        //$order_id = 'order_'.$lastOrder->original['response'][0]['order_id'];
        //app('App\Http\Controllers\RealTimeController')->fireBase($notification_data, 'orders/'.$order_id,1);

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

        return response()->json(apiResponseHandler($lastOrder->original['response'][0], 'Order Confirmed', 200));
    }

    public function getSingleOrder($orderId)
    {
        $response = Order::with(['orderDetails' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'order_details.item_id')
                ->select(
                    'order_details.order_detail_id',
                    'order_details.order_id',
                    'items.restaurant_id',
                    'items.item_id',
                    'items.item_name',
                    'order_details.item_count',
                    'items.item_price',
                    'items.tax_rate',
                    'items.item_image',
                    'items.item_thumbnail',
                    'items.item_thumbnail',
                    'items.item_description')
                ->whereNotNull('items.item_id')
                ->get();
        }])->where('orders.order_id', '=', $orderId)
            ->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
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
            foreach ($response[0]['orderDetails'] as $key => $value) {
                $value->order_item = OrderItem::where('order_detail_id', '=', $value->order_detail_id)
                    ->leftJoin('items', 'items.item_id', 'order_items.item_id')
                    ->select(
                        'order_items.order_item_id',
                        'order_items.order_detail_id',
                        'order_items.modifier_group_id',
                        'order_items.item_count',
                        'items.item_name',
                        'items.item_price',
                        'items.tax_rate',
                        'items.item_image',
                        'items.item_thumbnail',
                        'items.item_thumbnail',
                        'items.item_description')
                    ->get();
            }
        }

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function setCardDefault($cardId)
    {
        SavedCard::where('user_id', Auth::user()->id)->update([
            'is_default' => 0
        ]);
        SavedCard::where('id', $cardId)->update([
            'is_default' => 1
        ]);
        return response()->json(apiResponseHandler([], 'success', 200));
    }
}
