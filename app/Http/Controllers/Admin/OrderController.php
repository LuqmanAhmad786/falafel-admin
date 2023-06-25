<?php

namespace App\Http\Controllers\Admin;

use App\GlobalSettings;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\Notification;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\OrderItem;
use App\Models\OrderPayment;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use App\Models\SubscriptionPreference;
use App\Models\User\UserRewardItems;
use App\Models\UserRewards;
use App\OrderRefunds;
use App\User;
use Composer\DependencyResolver\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function orderListPage(Request $request)
    {
        $response = Order::with(['userDetails'])
            ->leftJoin('order_feedback','order_feedback.order_id','=','orders.order_id')
            ->select('orders.*','order_feedback.feedback');
        if ($request->has('name') && $request->input('name') != '') {
            $search = $request->input('name');
            $response = $response->where(function($query) use ($search){
                $query->where('orders.user_first_name', 'LIKE', '%'.$search.'%');
                $query->orWhere('orders.user_last_name', 'LIKE', '%'.$search.'%');
                $query->orWhere('orders.user_number', 'LIKE', '%'.$search.'%');
            });
        }
        if($request->has('orderrange') && $request->input('orderrange') != ''){
            $split_date = explode('-', $request->input('orderrange'));
            $start_date = date('Y-m-d 00:00:00', strtotime($split_date[0]));
            $end_date = date('Y-m-d 23:59:59', strtotime($split_date[1]));
            $response->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('orders.created_at', [$start_date, $end_date]);
            });
        }
        if($request->has('order_id') && $request->input('order_id') != ''){
            $response = $response->where('orders.order_id', '=', $request->input('order_id'));
        }
        if($request->has('type') && $request->input('type') != ''){
            $response = $response->where('orders.order_type', '=', $request->input('type'));
        }
        if($request->has('feedback') && $request->input('feedback') != ''){
            $response = $response->where('order_feedback.feedback', '=', $request->input('feedback'));
        }
        if(Session::get('my_restaurant') != 'all'){
            $response = $response->where('orders.restaurant_id', '=', Session::get('my_restaurant'));
        }
        $count = $response->count();
        $response = $response->orderBy('orders.order_id','desc')
            ->paginate(20);
        return view('dashboard.all-order', ['orders' => $response, 'count' => $count]);
    }

    public function loadTransaction()
    {
        $response = $response = Order::leftJoin('order_payments', 'order_payments.order_id', 'orders.order_id')
            ->leftJoin('users', 'users.id', 'orders.user_id')
            ->whereNotNull('order_payments.order_id')
            ->where('orders.restaurant_id', '=', Session::get('my_restaurant'))
            ->select('orders.restaurant_id', 'orders.user_id', 'orders.reference_id', 'order_payments.*', 'users.customer_id', 'orders.total_amount')
            ->orderBy('order_payments.order_id', 'DESC')
            ->paginate(20);
        return view('dashboard.all-transaction', ['all_transaction' => $response]);
    }
    public function loadStatements()
    {
        return view('dashboard.statement');
    }

    public function searchOrder(Request $request)
    {
        $keyword = $request->input('keyword');

        $searchOrder = Order::query();

        $orderDate = DB::raw("DATE_FORMAT(orders.created_at,'%m-%d-%Y') as order_date");
        $pickUpTime = DB::raw("orders.pickup_time as pickup_time");
        $preparationTime = DB::raw("TIME_FORMAT(FROM_UNIXTIME(orders.preparation_time+19800),'%h:%i %p') as preparation_time");

        $searchOrder
            ->with(['favoriteOrder'])
            ->where('orders.restaurant_id', '=', Session::get('my_restaurant'))
            ->leftJoin('users', 'users.id', 'orders.user_id')
            ->select('orders.order_id', 'orders.reference_id', 'orders.user_id',
                'orders.restaurant_id', 'orders.total_amount',
                'orders.status', 'orders.created_at', 'orders.user_first_name', 'orders.user_last_name','payment_status',
                $pickUpTime, $preparationTime, $orderDate,
                'users.*');

        if ($request->input('keyword')) {
            $searchOrder->where(function ($query) use ($keyword) {
                $query->where('orders.reference_id', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.pickup_time', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.total_amount', 'like', '%' . $keyword . '%')
                    ->orWhere('users.first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.last_name', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->input('order_status')) {
            $searchOrder->where('orders.status', '=', $request->input('order_status'));
        }

        if ($request->input('date_range')[0]) {
            $searchOrder->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%m/%d/%Y')"), $request->input('date_range'));
        }

        if ($request->input('date')) {
            $searchOrder->where(DB::raw("DATE_FORMAT(orders.created_at,'%m/%d/%Y')"), '=', $request->input('date'));
        }

        $statistics['all_pending'] = count(Order::where('status', '=', 1)->orWhere('status', '=', 2)->get());
        $statistics['today_pending'] = count(Order::where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(current_date,'%Y%c%d')"))
            ->where('status', '=', 1)
            ->orWhere('status', '=', 2)
            ->get());

        $response = $searchOrder->orderBy('order_id', 'DESC')->get();

        $limit = getPaginationLimit();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);
    }

    public function singleOrderPage($orderId)
    {

        $orderDate = DB::raw("DATE_FORMAT(orders.created_at,'%c %D %Y, %h:%i %p') as order_date");
        $pickUpTime = DB::raw("orders.pickup_time as pickup_time");
        $preparationTime = DB::raw("TIME_FORMAT(FROM_UNIXTIME(orders.preparation_time+19800),'%h:%i %p') as preparation_time");

        $response = Order::with(['userDetails', 'restaurantDetails', 'feedback', 'orderDetails' => function ($query) {
            $query->with(['orderItems'])
                ->get();
        }, 'transaction', 'refund'])->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->where('order_id', '=', $orderId)
            ->select('orders.order_id', 'orders.reference_id', 'orders.user_id',
                'orders.restaurant_id',
                'orders.user_email',
                'orders.user_first_name',
                'orders.user_last_name',
                'orders.user_number',
                'orders.address_line_1',
                'orders.order_type',
                'orders.order_device',
                'orders.delivery_fee',
                'orders.delivery_status',
                'orders.delivery_notes',
                'orders.delivery_address',
                'orders.postmates_delivery_id',
                'orders.postmates_tracking_url',
                'orders.is_server_order',
                DB::raw('FORMAT(orders.total_tax,2) as total_tax'),
                DB::raw('FORMAT(orders.order_total,2) as order_total'),
                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
                'reward_coupons.coupon_id',
                'reward_coupons.coupon_type',
                'orders.discount_amount',
                'orders.status', 'orders.created_at', 'orders.payment_status',
                $pickUpTime, $preparationTime, $orderDate)->first();
                $items = [];
        
        $rewards = UserRewards::where('order_id',$orderId)
            ->whereIn('type',[1,5])
            ->sum('total_rewards');
        return view('dashboard.single-order', ['single_order' => $response,'rewards' => $rewards , 'items' => $items]);
    }

    public function singleOrderDetail($orderId)
    {
        $orderDate = DB::raw("DATE_FORMAT(orders.created_at,'%m-%d-%Y') as order_date");
        $pickUpTime = DB::raw("orders.pickup_time as pickup_time");
        $preparationTime = DB::raw("LOWER(TIME_FORMAT(FROM_UNIXTIME(orders.preparation_time),'%l:%i %p')) as preparation_time");

        $response = Order::with(['userDetails', 'userReward', 'restaurantDetails', 'orderDetails' => function ($query) {
            $query->with(['orderItems'])->leftJoin('items', 'items.item_id', 'order_details.item_id')
                ->whereNotNull('items.item_id')
                ->get();
        }, 'transaction'])->leftJoin('reward_coupons', 'reward_coupons.coupon_id', 'orders.coupon_id')
            ->where('order_id', '=', $orderId)
            ->select('orders.order_id', 'orders.reference_id', 'orders.user_id',
                'orders.restaurant_id',
                'orders.user_first_name',
                'orders.user_last_name',
                DB::raw('FORMAT(orders.total_tax,2) as total_tax'),
                DB::raw('FORMAT(orders.order_total,2) as order_total'),
                DB::raw('FORMAT(orders.total_amount,2) as total_amount'),
                'orders.discount_amount',
                'reward_coupons.coupon_id',
                'reward_coupons.coupon_type',
                'orders.discount_amount',
                'orders.status', 'orders.created_at',
                $pickUpTime, $preparationTime, $orderDate)->first();
        if($response['pickup_time']){
            $response['pickup_time'] = date('g:i a',$response['pickup_time']);
        }
        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function markOrderPicked($orderId)
    {
        Order::where(['order_id' => $orderId])->update(['status' => 3]);

        /*Email Notification*/
        $getOrder = Order::where(['order_id' => $orderId])->first();
        $user = User::where('id', '=', $getOrder['user_id'])->first();
        $template = view('email-templates.order-completion', ['name' => $user->first_name])->render();
        $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $user->id)->first();
        if ($subscriptionPreference['email_subscription']) {
            sendEmail($template, $user->email, 'Order Completed.');
        }

        /*Push Notification*/
        $devicePreference = DevicePreference::where('user_id', '=', $user->id)->first();
        $tokens = FirebaseToken::where('user_id', '=', $user->id)->get();
        $receivers = FirebaseToken::where('user_id', '=', $user->id)->get();
        $notification = array(
            'message' => 'Hello,' . ' ' . $user->first_name . ' your order is picked up successfully! We would love to know your experience with last order.',
            'title' => 'Order Completed',
            'body' => 'Hello,' . ' ' . $user->first_name .' your order is picked up successfully! We would love to know your experience with last order.',
            'type' => 4,
            'data' => $getOrder,
            'sound' => 'default'
        );
        if ($devicePreference['push_notification']) {
            foreach ($tokens as $tk) {
                app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'],$notification['message'],[$tk['token']],['notification_type'=>4,'order_id'=>$orderId]);
            }
        }
        $this->storePushNotifications($receivers, $notification);
        return redirect('orders/details/' . $orderId);
    }

    public function storePushNotifications($receivers, $notification)
    {
        foreach ($receivers as $item) {
            Notification::create([
                'receiver_id' => $item['user_id'],
                'sender_id' => Auth::user() ? Auth::user()->id : 0,
                'message' => $notification['message'],
                'type' => $notification['type']
            ]);
        }
    }

    public function getTransactions(Request $request)
    {
        $response = Order::leftJoin('order_payments', 'order_payments.order_id', 'orders.order_id')
            ->leftJoin('users', 'users.id', 'orders.user_id')
            ->whereNotNull('order_payments.order_id')
            ->where('orders.restaurant_id', '=', Session::get('my_restaurant'))
            ->select('orders.restaurant_id', 'orders.user_id', 'orders.reference_id', 'order_payments.*', 'users.customer_id', 'orders.total_amount')
            ->orderBy('order_payments.order_id', 'DESC')
            ->get();

        $limit = getPaginationLimit();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);
    }

    public function orderRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'refund_amount' => 'required',
            'refund_type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $orderId = $request->input('order_id');

        $order = Order::find($orderId);

        if($request->input('refund_amount') > $order->total_amount){
            return response()->json(apiResponseHandler([], 'Refund amount should not greater than order total amount.', 400), 400);
        }

        if ($order->payment_status == 1) {
            $orderPayment = OrderPayment::where('order_id', $orderId)->first();
            if ($orderPayment) {

                $totalRefundAmount = $order->total_amount;
                $rewardMultiplier = 1;
                if ($order->bonus_id) {
                    $bonus = Bonus::where('bonus_id', $order->bonus_id)->first();
                    if ($bonus->bonus_type == 4) {
                        $totalRefundAmount = $totalRefundAmount - (($totalRefundAmount * $bonus->bonus_discount) / 100);
                    }
                    if ($bonus->bonus_type == 2) {
                        $rewardMultiplier = $bonus->bonus_points_multiplier;
                    }
                }

                $amountWT = $order->total_amount;

                if($request->input('refund_type') == "1"){
                    $totalRefundAmount = $request->input('refund_amount');
                }

//                $data = app('App\Http\Controllers\API\BtPaymentController')->makeRefund($orderPayment->order_code, $totalRefundAmount);
//                if(!$data){
//                    return response()->json(apiResponseHandler([], 'Something went wrong with Gateway. Please try again.', 400), 400);
//                }
//                $refund = new OrderRefunds();
//                $refund->order_id = $orderId;
//                $refund->transaction_id = $data->id;
//                $refund->transaction_status = $data->status;
//                $refund->approval_number = 0;
//                $refund->refund_amount = $totalRefundAmount;
//                $refund->sub_total_refund = $order->order_total;
//                $refund->tax_refund = $order->total_tax;
//                $refund->discount_adjust = $order->discount_amount * (-1);
//                $refund->reference_number = $data->id;
//                $refund->save();

                DB::table('order_refund_queue')->insert([
                    'order_id' => $orderId,
                    'transaction_id' => $orderPayment->order_code,
                    'amount' => $totalRefundAmount,
                    'order_total' => $order->order_total,
                    'total_tax' => $order->total_tax,
                    'discount_amount' => $order->discount_amount * (-1),
                    'status' => 0
                ]);

                OrderDetail::where('order_id', $orderId)->update(['is_refunded' => 1]);

                if ($order->user_id != 0) {
                    if($request->input('refund_type') == "1")
                    {
                        UserRewards::create([
                            'order_id' => $orderId,
                            'user_id' => $order->user_id,
                            'total_rewards' => -round($request->input('refund_amount')),
                            'month' => strtotime(date('Y-m', time()) . '-1'),
                            'type' => 5
                        ]);
                    }
                    else
                    {
                        UserRewards::where('order_id', $orderId)->where('type', 2)->delete();

                        RewardCoupon::where('user_id', $order->user_id)->where('status', 1)->where('coupon_type', 1)->delete();

                        UserRewards::create([
                            'order_id' => $orderId,
                            'user_id' => $order->user_id,
                            'total_rewards' => -round($amountWT),
                            'month' => strtotime(date('Y-m', time()) . '-1'),
                            'type' => 5
                        ]);
                    }
                }

                Order::where('order_id', $orderId)->update(['payment_status' => 4]);

                // CUSTOMER EMAIL

                $lastOrder = $this->getSingleOrder($orderId);


                $refundSubtotal = DB::Raw('SUM(sub_total_refund) AS refund_sub_total');
                $refundTotal = DB::Raw('SUM(refund_amount) AS refund_total');
                $refundTax = DB::Raw('SUM(tax_refund) AS refund_tax');
                $discountAdjust = DB::Raw('SUM(discount_adjust) AS discount_adjust');
                $orderRefund = OrderRefunds::select($refundSubtotal, $refundTotal, $refundTax, $discountAdjust)->where('order_id', $orderId)->first();

                if($request->input('refund_type') == "1")
                {
//                    $template = view('email-templates.partial-refund-amount', [
//                        'name' => $lastOrder->original['response'][0]['user_first_name'],
//                        'order_id' => $lastOrder->original['response'][0]['order_id'],
//                        'order_details' => $lastOrder->original['response'][0],
//                        'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                        'user_email' => $lastOrder->original['response'][0]['user_email'],
//                        'user_number' => $lastOrder->original['response'][0]['user_number'],
//                        'user_id' => 1,
//                        'refund_sub_total' => $orderRefund['refund_sub_total'],
//                        'refund_tax' => $orderRefund['refund_tax'],
//                        'refund_total' => $totalRefundAmount,
//                        'discount_adjust' => $orderRefund['discount_adjust'],
//                    ])->render();

                    // ADMIN EMAIL

//                    $adminTemplate = view('email-templates.admin-partial-refund-amount', [
//                        'name' => $lastOrder->original['response'][0]['user_first_name'],
//                        'order_id' => $lastOrder->original['response'][0]['order_id'],
//                        'order_details' => $lastOrder->original['response'][0],
//                        'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                        'user_email' => $lastOrder->original['response'][0]['user_email'],
//                        'user_number' => $lastOrder->original['response'][0]['user_number'],
//                        'user_id' => 1,
//                        'refund_sub_total' => $orderRefund['refund_sub_total'],
//                        'refund_tax' => $orderRefund['refund_tax'],
//                        'refund_total' => $totalRefundAmount,
//                        'discount_adjust' => $orderRefund['discount_adjust'],
//                    ])->render();
                }
                else
                {
//                    $template = view('email-templates.order-refund', [
//                        'name' => $lastOrder->original['response'][0]['user_first_name'],
//                        'order_id' => $lastOrder->original['response'][0]['order_id'],
//                        'order_details' => $lastOrder->original['response'][0],
//                        'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                        'user_email' => $lastOrder->original['response'][0]['user_email'],
//                        'user_number' => $lastOrder->original['response'][0]['user_number'],
//                        'user_id' => 1,
//                        'refund_sub_total' => $order->order_total,
//                        'refund_tax' => $order->total_tax,
//                        'refund_total' => $totalRefundAmount,
//                        'discount_adjust' => $order->discount_amount * (-1),
//                    ])->render();

                    // ADMIN EMAIL

//                    $adminTemplate = view('email-templates.order-refund-admin', [
//                        'name' => $lastOrder->original['response'][0]['user_first_name'],
//                        'order_id' => $lastOrder->original['response'][0]['order_id'],
//                        'order_details' => $lastOrder->original['response'][0],
//                        'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                        'user_email' => $lastOrder->original['response'][0]['user_email'],
//                        'user_number' => $lastOrder->original['response'][0]['user_number'],
//                        'user_id' => 1,
//                        'refund_sub_total' => $order->order_total,
//                        'refund_tax' => $order->total_tax,
//                        'refund_total' => $totalRefundAmount,
//                        'discount_adjust' => $order->discount_amount * (-1),
//                    ])->render();
                }
                // sendEmail($template, $lastOrder->original['response'][0]['user_email'], 'Falafel Corner: your Order #' . $orderId . ' was refunded $' . $totalRefundAmount . '.');

                // old notification message
                // 'Falafel Corner: Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $totalRefundAmount . '.'
                // old notification message
                $notification = array(
                    'message' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $totalRefundAmount . '.',
                    'title' => 'Order Refund',
                    'body' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $totalRefundAmount . '.',
                    'type' => 20,
                    'data' => $order,
                    'sound' => 'default'
                );

                if ($order->user_id != 0) {
                    $tokens = FirebaseToken::where('user_id', '=', $order->user_id)->get();
                    $devicePreference = DevicePreference::where('user_id', '=', $order->user_id)->first();

                    if ($devicePreference['push_notification'] && count($tokens) > 0) {
                        foreach ($tokens as $tk) {
                            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 20, 'order_id' => $orderId]);
                        }
                    }
                } else {
                    if($order->firebase_token){
                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$order->firebase_token], ['notification_type' => 20, 'order_id' => $orderId]);
                    }
                }

                return response()->json(apiResponseHandler([], 'Refunded Successfully.', 200), 200);
            }
        } else {
            return response()->json(apiResponseHandler([], 'Order can not be refunded.', 400), 400);
        }
    }

    public function partialOrderRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'items' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        $allItems = OrderDetail::whereIn('order_detail_id', $request->input('items'))->get();
        $allmodifierItems = OrderItem::whereIn('order_detail_id', $request->input('items'))->get();

        $refundAmount = 0;
        $refundAmountMods = 0;

        if (count($allItems) > 0) {
            foreach ($allItems as $item) {
                $refundAmount += $item->item_price;
            }
        }

        if (count($allmodifierItems) > 0) {
            foreach ($allmodifierItems as $item) {
                $refundAmountMods += $item->item_price;
            }
        }
        if ($refundAmount > 0) {
            $restaurant = Restaurant::find($order->restaurant_id);
            $taxVal = 8.25;
            if ($restaurant->tax_value) {
                $taxVal = $restaurant->tax_value;
            }
            $totalRefundAmount = $refundAmount + $refundAmountMods;
            $refundSubTotal = $totalRefundAmount;
            $rewardMultiplier = 1;
            $discountAmount = 0;
            if ($order->bonus_id) {
                $bonus = Bonus::where('bonus_id', $order->bonus_id)->first();
                if ($bonus->bonus_type == 4) {
                    $discountAmount = ($totalRefundAmount * $bonus->bonus_discount) / 100;
                    $totalRefundAmount = $totalRefundAmount - (($totalRefundAmount * $bonus->bonus_discount) / 100);
                }
                if ($bonus->bonus_type == 2) {
                    $rewardMultiplier = $bonus->bonus_points_multiplier;
                }
            }
            $amountWT = $totalRefundAmount;
            $refundTax = $totalRefundAmount * ($taxVal) / 100;
            $refundAmount = (float)number_format($totalRefundAmount + ($totalRefundAmount * ($taxVal) / 100), 2);

            $orderPayment = OrderPayment::where('order_id', $orderId)->first();
            if ($orderPayment) {
                DB::table('order_refund_queue')->insert([
                    'order_id' => $orderId,
                    'transaction_id' => $orderPayment->order_code,
                    'amount' => $refundAmount,
                    'order_total' => $refundSubTotal,
                    'total_tax' => $refundTax,
                    'discount_amount' => $discountAmount * (-1),
                    'status' => 0
                ]);

                Order::where('order_id', $orderId)->update(['payment_status' => 4]);

                OrderDetail::whereIn('order_detail_id', $request->input('items'))->update(['is_refunded' => 1]);

                if ($order->user_id != 0) {
                    UserRewards::where('order_id', $orderId)->where('type', 2)->delete();

                    RewardCoupon::where('user_id', $order->user_id)->where('status', 1)->where('coupon_type', 1)->delete();

                    UserRewards::create([
                        'order_id' => $orderId,
                        'user_id' => $order->user_id,
                        'total_rewards' => -round($amountWT),
                        'month' => strtotime(date('Y-m', time()) . '-1'),
                        'type' => 5
                    ]);
                }

                // CUSTOMER EMAIL

                $lastOrder = $this->getSingleOrder($orderId);

//                $template = view('email-templates.order-refund', [
//                    'name' => $lastOrder->original['response'][0]['user_first_name'],
//                    'order_id' => $lastOrder->original['response'][0]['order_id'],
//                    'order_details' => $lastOrder->original['response'][0],
//                    'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                    'user_email' => $lastOrder->original['response'][0]['user_email'],
//                    'user_number' => $lastOrder->original['response'][0]['user_number'],
//                    'user_id' => 1,
//                    'refund_sub_total' => $refundSubTotal,
//                    'refund_tax' => $refundTax,
//                    'refund_total' => $refundAmount,
//                    'discount_adjust' => $discountAmount * (-1),
//                ])->render();
//                sendEmail($template, $lastOrder->original['response'][0]['user_email'], 'Falafel Corner: your Order #' . $orderId . ' was refunded $' . $refundAmount . '.');

                // ADMIN EMAIL

//                $adminTemplate = view('email-templates.order-refund-admin', [
//                    'name' => $lastOrder->original['response'][0]['user_first_name'],
//                    'order_id' => $lastOrder->original['response'][0]['order_id'],
//                    'order_details' => $lastOrder->original['response'][0],
//                    'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                    'user_email' => $lastOrder->original['response'][0]['user_email'],
//                    'user_number' => $lastOrder->original['response'][0]['user_number'],
//                    'user_id' => 1,
//                    'refund_sub_total' => $refundSubTotal,
//                    'refund_tax' => $refundTax,
//                    'refund_total' => $refundAmount,
//                    'discount_adjust' => $discountAmount * (-1),
//                ])->render();

                $notification = array(
                    'message' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $refundAmount . '.',
                    'title' => 'Order Refund',
                    'body' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $refundAmount . '.',
                    'type' => 20,
                    'data' => $order,
                    'sound' => 'default'
                );

                if ($order->user_id != 0) {
                    $tokens = FirebaseToken::where('user_id', '=', $order->user_id)->get();
                    $devicePreference = DevicePreference::where('user_id', '=', $order->user_id)->first();

                    if ($devicePreference['push_notification'] && count($tokens) > 0) {
                        foreach ($tokens as $tk) {
                            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 20, 'order_id' => $orderId]);
                        }
                    }
                } else {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$order->firebase_token], ['notification_type' => 20, 'order_id' => $orderId]);
                }

                return response()->json(apiResponseHandler([], 'Refunded Successfully.', 200), 200);
            }
        }
        return response()->json(apiResponseHandler([], 'Items not found', 400), 400);
    }

    public function partialItemRefund(Request $request){
       
        $data = $request->all();

        $refundItems = [];

        if(!$request->has('item_check')){
            return Redirect::back();
        }

        foreach ($data['item_check'] AS $key => $value){
            if($value == 1){
                array_push($refundItems,$key);
            }
        }

        $orderId = $request->input('order_id');
        $order = Order::find($orderId);


        $allItems = OrderDetail::whereIn('order_detail_id', $refundItems)->get();
        $allmodifierItems = OrderItem::whereIn('order_detail_id', $refundItems)->get();

        $refundAmount = 0;
        $refundAmountMods = 0;

        $refundItemsLength = count($data['item_check']);
        $orderItemsLength = OrderDetail::where('order_id',$orderId)
            ->where('item_flag',0)
            ->count();

        if (count($allItems) > 0) {
            foreach ($allItems as $item) {
                $orderDetailId = $item->order_detail_id;
                if (isset($data['item_qty'][$orderDetailId])) {
                    $itemQty = $data['item_qty'][$orderDetailId];
                    $refundAmount += $item->item_price * $itemQty;
                } else {
                    echo "Missing item_qty for order_detail_id: " . $orderDetailId;
                }
                // $itemQty = $data['item_qty'][$item->order_detail_id];
                // $refundAmount += $item->item_price * $itemQty;
            }
        }

        if (count($allmodifierItems) > 0) {
            foreach ($allmodifierItems as $item) {
                $orderDetailId = $item->order_detail_id;
                if (isset($data['item_qty'][$orderDetailId])) {
                    $itemQty = $data['item_qty'][$orderDetailId];
                    $refundAmountMods += $item->item_price * $itemQty;
                } else {
                    echo "Missing item_qty for order_detail_id: " . $orderDetailId;
                }
            }
                // $itemQty = $data['item_qty'][$item->order_detail_id];
                // $refundAmountMods += $item->item_price*$itemQty;
            }
        

        if ($refundAmount > 0) {
            $restaurant = Restaurant::find($order->restaurant_id);
            $taxVal = 8.25;
            if($restaurant->tax_rate){
                $taxVal = $restaurant->tax_rate;
            }
            $totalRefundAmount = $refundAmount + $refundAmountMods;
            $refundSubTotal = $totalRefundAmount;
            $rewardMultiplier = 1;
            $discountAmount = 0;
            if ($order->bonus_id) {
                $bonus = Bonus::where('bonus_id', $order->bonus_id)->first();
                if ($bonus->bonus_type == 4) {
                    $discountAmount = ($totalRefundAmount * $bonus->bonus_discount) / 100;
                    $totalRefundAmount = $totalRefundAmount - (($totalRefundAmount * $bonus->bonus_discount) / 100);
                }
                if ($bonus->bonus_type == 2) {
                    $rewardMultiplier = $bonus->bonus_points_multiplier;
                }
            }
            $amountWT = $totalRefundAmount;
            $refundTax = round($totalRefundAmount * ($taxVal) / 100,2);
            $refundAmount = (float)number_format($totalRefundAmount + ($totalRefundAmount * ($taxVal) / 100), 2);

            $orderPayment = OrderPayment::where('order_id', $orderId)->first();
            if ($orderPayment) {
                DB::table('order_refund_queue')->insert([
                    'order_id' => $orderId,
                    'transaction_id' => $orderPayment->order_code,
                    'amount' => $refundAmount,
                    'order_total' => $refundSubTotal,
                    'total_tax' => $refundTax,
                    'discount_amount' => $discountAmount * (-1),
                    'status' => 0
                ]);

                Order::where('order_id', $orderId)->update(['payment_status' => 4]);

                foreach ($allItems as $item) {
                    $itemQty = $data['item_qty'][$item->order_detail_id];
                    OrderDetail::where('order_detail_id', $item->order_detail_id)
                        ->update([
                            'is_refunded' => 1,
                            'refunded_qty' => $itemQty
                        ]);
                }

                if ($order->user_id != 0) {
                    if($refundItemsLength == $orderItemsLength){
                        UserRewards::where('order_id', $orderId)->where('type', 2)->delete();

                        $rewardItem = OrderDetail::where('order_id',$orderId)
                            ->where('item_flag',1)->first();
                        if($rewardItem){
                            $rewardItemId = RewardsItem::where('item_id',$rewardItem->item_id)
                                ->first();
                            UserRewardItems::where('user_id',$order->user_id)
                                ->where('reward_item_id', $rewardItemId->id)
                                ->delete();
                        }
                    }

                    UserRewards::create([
                        'order_id' => $orderId,
                        'user_id' => $order->user_id,
                        'total_rewards' => -round($amountWT,2),
                        'month' => strtotime(date('Y-m', time()) . '-1'),
                        'type' => 5
                    ]);

                    $userData = User::find($order->user_id);
                    $userData->membership_points = $userData->membership_points - $amountWT;
                    $userData->save();
                }

                // CUSTOMER EMAIL

                $lastOrder = $this->getSingleOrder($orderId);

//                $template = view('email-templates.order-refund', [
//                    'name' => $lastOrder->original['response'][0]['user_first_name'],
//                    'order_id' => $lastOrder->original['response'][0]['order_id'],
//                    'order_details' => $lastOrder->original['response'][0],
//                    'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                    'user_email' => $lastOrder->original['response'][0]['user_email'],
//                    'user_number' => $lastOrder->original['response'][0]['user_number'],
//                    'user_id' => 1,
//                    'refund_sub_total' => $refundSubTotal,
//                    'refund_tax' => $refundTax,
//                    'refund_total' => $refundAmount,
//                    'discount_adjust' => $discountAmount * (-1),
//                ])->render();
//                sendEmail($template, $lastOrder->original['response'][0]['user_email'], 'Falafel Corner: your Order #' . $orderId . ' was refunded $' . $refundAmount . '.');

                // ADMIN EMAIL

//                $adminTemplate = view('email-templates.order-refund-admin', [
//                    'name' => $lastOrder->original['response'][0]['user_first_name'],
//                    'order_id' => $lastOrder->original['response'][0]['order_id'],
//                    'order_details' => $lastOrder->original['response'][0],
//                    'user_name' => $lastOrder->original['response'][0]['user_first_name'] . ' ' . $lastOrder->original['response'][0]['user_last_name'],
//                    'user_email' => $lastOrder->original['response'][0]['user_email'],
//                    'user_number' => $lastOrder->original['response'][0]['user_number'],
//                    'user_id' => 1,
//                    'refund_sub_total' => $refundSubTotal,
//                    'refund_tax' => $refundTax,
//                    'refund_total' => $refundAmount,
//                    'discount_adjust' => $discountAmount * (-1),
//                ])->render();

                $notification = array(
                    'message' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $refundAmount . '.',
                    'title' => 'Order Refund',
                    'body' => 'Hi ' . $order->user_first_name . ', your Order #' . $orderId . ' was refunded $' . $refundAmount . '.',
                    'type' => 20,
                    'data' => $order,
                    'sound' => 'default'
                );

                if ($order->user_id != 0) {
                    $tokens = FirebaseToken::where('user_id', '=', $order->user_id)->get();
                    $devicePreference = DevicePreference::where('user_id', '=', $order->user_id)->first();

                    if ($devicePreference['push_notification'] && count($tokens) > 0) {
                        foreach ($tokens as $tk) {
                            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 20, 'order_id' => $orderId]);
                        }
                    }
                } else {
                    if($order->firebase_token){
                        app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$order->firebase_token], ['notification_type' => 20, 'order_id' => $orderId]);
                    }
                }

                return Redirect::back();
            }
        }
        return Redirect::back();
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


 