<?php

namespace App\Console\Commands;

use App\GlobalSettings;
use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Restaurant;
use App\Models\SubscriptionPreference;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BeforePreparationTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beforePickupTime:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allOrder = Order::where('status', '=', 1)->get();
        foreach ($allOrder as $item) {
            $gSettings = GlobalSettings::first();
            if (time() + $gSettings->pickup_notification_time * 60 > $item['pickup_time']) {

                Order::where('order_id', '=', $item['order_id'])->update([
                    'status' => 2
                ]);

                //change status on firebase
                $notification_data = array(
                    'status' => 2
                );
                $order_id = 'order_' . $item['order_id'];
                app('App\Http\Controllers\RealTimeController')->fireBase($notification_data, 'orders_list/' . $order_id, 2);


                $token = FirebaseToken::where('user_id', '=', $item['user_id'])->get();
                $receivers = FirebaseToken::where('user_id', '=', $item['user_id'])->get();
                $users = User::where('id', '=', $item['user_id'])->first();


                /*Email Notification*/
                $restaurant = Restaurant::where('id', '=', $item['restaurant_id'])->first();
                $lastOrder = $this->getSingleOrder($item['order_id']);
                /*Email Notification*/
                $template = view('email-templates.order-completion',
                    [
                        'name' => $item['user_name'],
                        'total_reward' => '',
                        'pickup_time' => date('g:i a', $item['pickup_time']),
                        'restaurant' => $restaurant['address'],
                        'restaurant_id' => $restaurant['id'],
                        'restaurant_contact' => $restaurant['contact_number'],
                        'order_details' => $lastOrder->original['response'][0],
                        'user_id' => $item['user_id']
                    ]
                )->render();

                if ($item['user_id'] != 0) {
                    $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $item['user_id'])->first();
                    if ($subscriptionPreference['email_subscription']) {
                        sendEmail($template, $users['email'], 'Your Order is Ready for Pickup: Falafel Corner');
                    }
                } else {
                    sendEmail($template, $item['user_email'], 'Your Order is Ready for Pickup: Falafel Corner');
                }

                if ($item['user_id'] != 0) {
                    /*Push Notification*/
                    $devicePreference = DevicePreference::where('user_id', '=', $item['user_id'])->first();
                    $notification = array(
                        'message' => 'Hi' . ' ' . $users['first_name'] . ', ' . 'your order is ready for pickup.  Get ready for the most delicious part of your day!',
                        'title' => 'Order Ready For Pickup.',
                        'body' => 'Hi' . ' ' . $users['first_name'] . ', ' . 'your order is ready for pickup.  Get ready for the most delicious part of your day!',
                        'type' => 10,
                        'data' => (object)array(),
                        'sound' => 'default'
                    );
                    if ($devicePreference['push_notification']) {
                        foreach ($token as $tk) {
                            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 10, 'order_id' => $item['order_id']]);
                        }
                    }
                    app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);
                } else {
                    $notification = array(
                        'message' => 'Hi' . ' ' . $item['user_first_name'] . ', ' . 'your order is ready for pickup.  Get ready for the most delicious part of your day!',
                        'title' => 'Order Ready For Pickup.',
                        'body' => 'Hi' . ' ' . $item['user_first_name'] . ', ' . 'your order is ready for pickup.  Get ready for the most delicious part of your day!',
                        'type' => 10,
                        'data' => (object)array(),
                        'sound' => 'default'
                    );
                    if ($item['firebase_token']) {
                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$item['firebase_token']], ['notification_type' => 10, 'order_id' => $item['order_id']]);
                    }
                }
            }
        }
    }

    public function getSingleOrder($orderId)
    {
        $response = \App\Models\Order\Order::with(['orderDetails'])->where('orders.order_id', '=', $orderId)->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
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
}
