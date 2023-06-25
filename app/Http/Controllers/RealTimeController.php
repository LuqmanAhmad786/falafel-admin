<?php

namespace App\Http\Controllers;

use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\ManageNotifications;
use App\Models\Order;
use App\Models\Order\OrderItem;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\SubscriptionPreference;
use App\Models\UserRewards;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging;
use App\User;
use Jenssegers\Agent\Agent;

class RealTimeController extends Controller
{
    public function fireBase($notification_data, $table, $type)
    {
//        $database = app('firebase.database');
//        if ($type == 1) {
//            $database->getReference($table)->set($notification_data);
//
//        } else if ($type == 2) {
//            $database->getReference($table)->update($notification_data);
//        }

        return true;
    }

    public function removeFcmTable()
    {
//        $serviceAccount = ServiceAccount::fromJsonFile('./farmer-s-fresh-kitchen-firebase-adminsdk-eoicq-1aeffac5ae.json');
//        $firebase = (new Factory)
//            ->withServiceAccount($serviceAccount)
//            ->withDatabaseUri('https://farmer-s-fresh-kitchen.firebaseio.com')
//            ->create();
//        $database = $firebase->getDatabase();
//        $database->getReference('orders')->remove();
//        $database = app('firebase.database');

//        $database->getReference('orders')->remove();
    }

    public function cloudNotifications($title = 'Test', $body = 'test', $tokens = '', $data = [])
    {
        if (!empty($data)) {
            $data = ['data' => json_encode($data)];
        } else {
            $data = ['data' => ''];
        }
        $notification = Notification::create($title, $body);
        foreach ($tokens AS $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)->withData($data);
                $messaging = app('firebase.messaging');
                $m = $messaging->send($message);
            } catch (Exception $e) {

            }
        }
        return true;
    }

    public function test(Request $request)
    {
        $restaurant = Restaurant::where('id', '=', 1)->first();
        $lastOrder = $this->getSingleOrder(1);
        return view('email-templates.guest-checkout', [
            'name' => 'test',
            'sender' => 'test',
            'gift_card_number' => '2892892892',
            'gift_card_code' => '383838',
            'total_reward' => 0,
            'order_id' => $lastOrder->original['response'][0]['order_id'],
            'pickup_time' => 1001,
            'preparation_time' => 1001,
            'restaurant' => $restaurant['address'],
            'restaurant_id' => $restaurant['id'],
            'restaurant_contact' => $restaurant['contact_number'],
            'order_details' => $lastOrder->original['response'][0],
            'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
            'user_email' => $lastOrder->original['response'][0]['user_email'],
            'user_number' => $lastOrder->original['response'][0]['user_number'],
            'user_id' => 1,
            'link' => 1
        ]);
        //sendEmail($template, 'kapil@qualwebs.com', 'Order template');
        //$users = $this->getBonusUsers($request->input('bonus_condition'),$request);
        //return response()->json(apiResponseHandler(count($users), 'success', 200), 200);
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

    public function getBonusUsers($condition, $request)
    {
        if ($condition == 8) {
            $users = User::whereRaw("DATE_FORMAT(FROM_UNIXTIME(date_of_birth),'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')")->pluck('id');
            return $users;
        }

        if ($condition == 7) {
            $users = User::leftJoin('user_rewards', 'user_rewards.user_id', 'users.id')
                ->select(DB::raw('SUM(user_rewards.total_rewards) as points'), 'users.id')
                ->having('points', '>=', $request->input('bonus_user_points'))
                ->groupBy('users.id')
                ->pluck('users.id');
            return $users;
        }

        if ($condition == 4) {
            $order = Order::query();
            $order = $order->select(
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0)->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('bonus_start_date'), $request->input('bonus_end_date')));
            $order = $order->groupBy('orders.user_id');
            return $order->pluck('user_id');
        }

        if ($condition == 5) {
            $order = Order::query();
            $order = $order->select(
                DB::raw('COUNT(orders.user_id) as count'),
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0)->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('bonus_start_date'), $request->input('bonus_end_date')));
            $order = $order->having('count', '=', $request->input('bonus_orders_no'));
            $order = $order->groupBy('orders.user_id');
            return $order->pluck('user_id');
        }

        if ($condition == 2) {
            $order = Order::query();
            $order = $order->select(
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0)->where(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('order_date')));
            $order = $order->groupBy('orders.user_id');
            return $order->pluck('user_id');
        }

        if ($condition == 3) {
            $order = Order::query();
            $order = $order->select(
                'order_id',
                'orders.user_id',
                DB::raw("DATE_FORMAT(orders.created_at,'%h:%i %p') AS time")
            );
            $order = $order->where('orders.user_id', '!=', 0)
                ->where(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('order_date')))
                ->where(DB::raw("DATE_FORMAT(orders.created_at,'%h:%i %p')"), array($request->input('order_time')));
            $order = $order->groupBy('orders.user_id');
            return $order->pluck('user_id');
        }

        if ($condition == 9) {
            $order = Order::query();
            $order = $order->select(
                DB::raw('COUNT(orders.user_id) as count'),
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0);
            $order = $order->having('count', '=', $request->input('bonus_orders_no') - 1);
            $order = $order->groupBy('orders.user_id');
            return $order->pluck('user_id');
        }

        if ($condition == 10) {
            $order = Order::query();
            $order = $order->select(
                DB::raw('SUM(orders.total_amount) as total_amount'),
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0);
            $order = $order->groupBy('orders.user_id');
            $order = $order->having('total_amount', '>', $request->input('total_order_amount'));
            return $order->pluck('user_id');
        }

        if ($condition == 11) {
            $order = Order::query();
            $order = $order->select(
                DB::raw('SUM(orders.total_amount) as total_amount'),
                'order_id',
                'orders.user_id'
            );
            $order = $order->where('orders.user_id', '!=', 0);
            $order = $order->groupBy('orders.user_id');
            $order = $order->having('total_amount', '<', $request->input('total_order_amount'));
            return $order->pluck('user_id');
        }
        if ($condition == 12) {
//            $order = Order::query();
//            $order = $order->select(
//                DB::raw('COUNT(orders.user_id) as count'),
//                'order_id',
//                'orders.user_id'
//            );
//            $order = $order->where('orders.user_id','!=',0)->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('bonus_start_date'), $request->input('bonus_end_date')));
////            $order = $order->having('count','=',$request->input('bonus_orders_no'));
//            $order = $order->groupBy('orders.user_id');
//            return $order->get();
            $users = User::query();
            $users = $users->leftJoin('orders', 'orders.user_id', '=', 'users.id');
            $users = $users->select(
                'users.id',
                DB::raw('COUNT(orders.user_id) as count')
            );
//            $users = $users->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($request->input('bonus_start_date'), $request->input('bonus_end_date')));
            $users = $users->where(DB::raw("DATE_FORMAT(users.created_at,'%Y-%m-%d')"), '<', $request->input('order_date'));
            $users = $users->groupBy('users.id');
            return $users->get();
        }
    }
}
