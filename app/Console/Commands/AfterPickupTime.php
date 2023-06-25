<?php

namespace App\Console\Commands;

use App\GlobalSettings;
use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\Order\Order;
use App\Models\Restaurant;
use App\Models\SubscriptionPreference;
use App\User;
use Illuminate\Console\Command;

class AfterPickupTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afterPickupTime:send';

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
        $allOrder = Order::where('status', '=', 2)->get();
        foreach ($allOrder as $item) {
//            $restaurant = Restaurant::where('id', '=', $item['restaurant_id'])->first();
            $gSettings = GlobalSettings::first();
            if (time() > $item['pickup_time'] + $gSettings->feedback_notification_time * 60) {

                Order::where('order_id', '=', $item['order_id'])->update([
                    'status' => 3
                ]);

                //change status on firebase
                $notification_data = array(
                    'status' => 3
                );
//                $order_id = 'order_' . $item['order_id'];
//                app('App\Http\Controllers\RealTimeController')->fireBase($notification_data, 'orders_list/' . $order_id, 2);
//
//
//                $token = FirebaseToken::where('user_id', '=', $item['user_id'])->get();
//                $receivers = FirebaseToken::where('user_id', '=', $item['user_id'])->get();
//                $users = User::where('id', '=', $item['user_id'])->first();
//
//                /*Email Notification*/
//                $template = view('email-templates.order-feedback',
//                    ['name' => $users['first_name'], 'order_id' => $item['order_id']]
//                )->render();
//
//                $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $item['user_id'])->first();
//                if ($subscriptionPreference['email_subscription']) {
//                    sendEmail($template, $users['email'], 'How Was Your Experience?: Falafel Corner');
//                }
//
//                /*Push Notification*/
//                $devicePreference = DevicePreference::where('user_id', '=', $item['user_id'])->first();
//                $notification = array(
//                    'message' => 'Your order has been picked up successfully. we\'d love to hear about your Falafel Corner experience!',
//                    'title' => 'How Was Your Experience?',
//                    'body' => 'Your order has been picked up successfully. we\'d love to hear about your Falafel Corner experience!',
//                    'type' => 4,
//                    'data' => $item['order_id'],
//                    'sound' => 'default'
//                );
//                if ($devicePreference['push_notification']) {
//                    foreach ($token as $tk) {
//                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 5, 'order_id' => $item['order_id']]);
//
//                    }
//                }
//                app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);
            }
        }
    }
}
