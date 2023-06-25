<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AssignAdminReward;
use App\Models\BonusAppliedFor;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\DevicePreference;
use App\Models\Favorite\FavoriteLabel;
use App\Models\Favorite\FavoriteOrder;
use App\Models\FavoriteRestaurant;
use App\Models\FirebaseToken;
use App\Models\Order\Order;
use App\Models\Order\OrderFeedback;
use App\Models\Order\OrderItem;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\User\UserCard;
use App\Models\UserPreference;
use App\Models\UserRewards;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function userPage(Request $request)
    {
        $keyword = $request->input('keyword');
        $id = $request->input('id');
        $searchUser = User::query();

        $restaurantUsers = [];
        if(Session::get('my_restaurant') != 'all'){
            $restaurantUsers = Order::where('user_id','!=',0)
                ->where('restaurant_id',Session::get('my_restaurant'))
                ->groupBy('user_id')->pluck('user_id')->toArray();
        }

        if ($keyword) {
            $searchUser->where(function ($query) use ($keyword) {
                $query->where('users.first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.email', 'like', '%' . $keyword . '%')
                    ->orWhere('users.mobile', 'like', '%' . $keyword . '%');
            });
        }
        if ($id) {
            $searchUser->where('users.id', '=', $id);
        }

        if(Session::get('my_restaurant') != 'all'){
            $searchUser->whereIn('id',$restaurantUsers);
        }

        $count = $searchUser->count();
        $searchUser
            ->orderBy('users.id', 'DESC')
            ->groupBy('users.id');

        $response = $searchUser->paginate(20);

        return view('dashboard.all-user', ['users' => $response, 'count' => $count]);
    }

    public function guests(Request $request)
    {
        $keyword = $request->input('keyword');
        $response = Order::select('user_email','user_first_name','user_last_name','user_number','created_at');
        if ($keyword) {
            $response->where(function ($query) use ($keyword) {
                $query->where('orders.user_first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.user_last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.user_email', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.user_number', 'like', '%' . $keyword . '%');
            });
        }
        if(Session::get('my_restaurant') != 'all'){
            $response->where('restaurant_id',Session::get('my_restaurant'));
        }

        $response = $response->orderBy('orders.created_at','DESC')->groupBy('user_email')->paginate(15);
        return view('dashboard.all-guest', ['users' => $response]);
    }

    function searchUser(Request $request)
    {
        $keyword = $request->input('keyword');
        $searchUser = User::query();

        if ($request->input('keyword')) {
            $searchUser->where(function ($query) use ($keyword) {
                $query->where('users.first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.email', 'like', '%' . $keyword . '%')
                    ->orWhere('users.mobile', 'like', '%' . $keyword . '%')
                    ->orWhere('users.zip_code', 'like', '%' . $keyword . '%');
            });
        }

        $response = $searchUser->orderBy('id', 'DESC')
            ->get();

        foreach ($response as $item) {
            $item['orders'] = count(Order::where('user_id', '=', $item['id'])->get());
            $item['rewards'] = UserRewards::where('user_id', '=', $item['id'])
                ->select(DB::raw('SUM(total_rewards) as points'))
                ->first();
            $item['rewards'] = $item['rewards']->points;
            $item['date_of_birth'] = date('F d Y', $item['date_of_birth']);
        }
        $limit = getPaginationLimit();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);
    }

    public function userDetailPage($userId)
    {
        $response = User::find($userId);
        /*$rewards = UserRewards::where('user_id', '=', $userId)->first();*/

        /*User orders*/
        $orders = Order::with(['restaurantDetails','orderDetails' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'order_details.item_id')
                ->select(
                    'order_details.order_detail_id',
                    'order_details.order_id',
                    'items.restaurant_id',
                    'items.item_id',
                    'items.item_name',
                    'items.item_price',
                    'items.tax_rate',
                    'items.item_image',
                    'items.item_thumbnail',
                    'items.item_thumbnail',
                    'items.item_description'
                )
                ->whereNotNull('items.item_id')
                ->get();
        }])->leftJoin('favorite_orders', 'favorite_orders.order_id', 'orders.order_id')
            ->leftJoin('order_payments', 'order_payments.order_id', 'orders.order_id')
            ->leftJoin('restaurants', 'restaurants.id', 'orders.restaurant_id')
            ->where('orders.user_id', '=', $userId)
            ->select('orders.order_id', 'orders.reference_id',
                'orders.pickup_time', 'orders.total_amount',
                'restaurants.name',
                'orders.status',
                'orders.total_tax',
                'orders.order_total',
                'orders.total_amount',
                'orders.created_at',
                'order_payments.card_scheme_type',
                DB::raw('(CASE WHEN favorite_orders.favorite_order_id is NULL THEN 0 ELSE 1 END) as is_favorite'),
                'favorite_orders.favorite_order_id', 'favorite_orders.favorite_label_id')
            ->orderBy('order_id','desc')->get();

        foreach ($orders as $item) {
            $item->order_date = strtotime($item->created_at);
            $item->order_date = date('F d Y, h:i A', $item->order_date);

            foreach ($item->orderDetails as $key => $value) {
                $value->order_item = OrderItem::where('order_detail_id', '=', $value->order_detail_id)
                    ->select(
                        'order_items.order_item_id',
                        'order_items.order_detail_id',
                        'order_items.item_name',
                        'order_items.item_price',
                        'order_items.item_count',
                        'order_items.item_image',
                        'order_items.item_description')
                    ->get();
            }
        };

        /*User Rewards*/
        $rewards['total_rewards'] = 0;
//        $rewards['list'] = UserRewards::with(['rewards' => function ($query) use ($userId) {
//            $query->where('user_id', '=', $userId)
//                /*->where('type', '=', 1)*/
//                ->select('order_id', 'user_id', 'total_rewards', 'month', 'created_at', 'type',
//                    DB::raw('DATE_FORMAT(created_at, \'%M %D %Y\') as order_date'))
//                ->orderBy('created_at','desc')
//                ->get();
//        }])->where('user_id', '=', $userId)
//            ->get();
        $rewards['list'] = UserRewards::where('user_id', '=', $userId)->orderBy('created_at','desc')->get();

        /*$rewards['list'] = UserRewards::where('user_id', '=', $userId)
            ->groupBy('month')
            ->where('type', '=', 1)
            ->select('order_id', 'user_id', 'total_rewards', 'month', 'created_at',
                DB::raw('DATE_FORMAT(created_at, \'%W, %D %M %Y\') as order_date'))
            ->get();*/

        $rewards['total_rewards'] = UserRewards::where('user_id', '=', $userId)
            ->select(DB::raw('SUM(total_rewards) as points'))
            ->first();

        $rewards['saved_rewards'] = RewardCoupon::where('user_id', '=', $userId)->orderBy('created_at','desc')->get();
        $rewards['saved_bonus'] = BonusAppliedFor::leftJoin('bonus','bonus.bonus_id','bonus_applied_for.bonus_id')->where('bonus_applied_for.user_id', '=', $userId)->orderBy('bonus_applied_for.created_at','desc')->get();

        $cartId = CartList::where('user_id', '=', $userId)
            ->orderBy('cart_list_id', 'DESC')->first();
        $response['cart_data'] = []; $response['cart_details'] = []; $response['user_cards'] = [];
        if($cartId){
            $response['cart_data'] = (new CartItem())
                ->with(['details' => function ($query) {
                    $query->leftJoin('modifier_items', 'modifier_items.id', 'cart_details.item_id');
                }])
                ->leftJoin('items', 'items.item_id', 'cart_items.item_id')
                ->where('cart_list_id', $cartId->cart_list_id)
                ->get();

            $response['cart_details'] = CartList::where('cart_list_id', '=', $cartId->cart_list_id)
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
                    'reward_coupons.coupon_type',
                    'cart_lists.updated_at'
                )
                ->first();
        }

        $response['user_cards'] = UserCard::with(['giftCard'])->where('user_id',$userId)->get();
//        dd($orders);
        return view('dashboard.single-user', ['single_user' => $response, 'all_orders' => $orders, 'rewards' => $rewards]);
    }

    public function managerPage()
    {
        $response = Admin::with(['location'])->get();
        $count = $response->count();
        $location = Restaurant::get();
        return view('dashboard.all-manager', ['managers' => $response, 'location' => $location, 'count' => $count]);
    }

    public function serverUserPage()
    {
        $response = User::leftJoin('restaurants','restaurants.id','=','users.assigned_restaurant')
            ->select('users.id','users.first_name','users.last_name','users.email','users.assigned_restaurant','users.is_account_deleted','restaurants.name')
            ->where('is_server_user',1)->get();
        $count = $response->count();
        $location = Restaurant::get();
        return view('dashboard.all-server-users', ['managers' => $response, 'location' => $location, 'count' => $count]);
    }

    public function addNewServerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'assigned_location' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $fullName = $request->input('name');

        $fullName  = explode(' ',$fullName);
        $firstName = '';
        $lastName = '';
        if(count($fullName) > 1){
            $firstName = $fullName[0];
            $lastName = $fullName[1];
        }
        if(count($fullName) == 1){
            $firstName = $fullName[0];
            $lastName = $fullName[0];
        }
        $user = new \App\User();
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $request->input('email');
        $user->mobile = '00000000';
        $user->password = Hash::make($request->input('password'));
        $user->customer_id = 'ser_' . generateCustomerId();
        $user->is_server_user = 1;
        $user->assigned_restaurant = $request->input('assigned_location');
        $user->save();

        return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
    }

    public function updateServerUserStatus(Request $request){
        User::where('id',$request->input('userId'))
            ->update(['is_account_deleted' => $request->input('status')]);
        $user = User::find($request->input('userId'));
        $user->tokens->each(function($token, $key) {
            $token->delete();
        });
        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function addNewManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admins',
            'username' => 'required|unique:admins,username',
            'assigned_location' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $isAlreadyAssign = Admin::where('assigned_location', '=', $request->input('assigned_location'))->get();

        if (count($isAlreadyAssign)) {
            return response()->json(apiResponseHandler([], 'Manager is already assigned for this locations.', 400), 400);
        } else {
            $admins = new Admin();
            $admins->name = $request->input('name');
            $admins->username = $request->input('username');
            $admins->email = $request->input('email');
            $admins->password = Hash::make($request->input('password'));
            $admins->type = 2;
            $admins->assigned_location = $request->input('assigned_location');
            $admins->save();
        }

        return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
    }

    public function updateManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:admins,id',
            'username' => 'required|unique:admins,username,'.$request->input('id').',id',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $admin = Admin::where('id',$request->input('id'))->first();
        if($request->filled('password')){
            $password = Hash::make($request->input('password'));
        } else{
            $password = $admin->password;
        }

        Admin::where('id', '=', $request->input('id'))->update([
            'username' => $request->input('username'),
            'password' => $password,
            'assigned_location' => $request->input('assigned_location')
        ]);

        return response()->json(apiResponseHandler([], 'Update Successfully', 200), 200);
    }

    public function deleteUser($userId)
    {
        $user = User::where('id', '=', $userId)->first();
        User::where('id', '=', $userId)->delete();
        AssignAdminReward::where('user_id', '=', $userId)->delete();
        BonusAppliedFor::where('user_id', '=', $userId)->delete();
        CartList::where('user_id', '=', $userId)->delete();
        DevicePreference::where('user_id', '=', $userId)->delete();
        FavoriteLabel::where('added_by', '=', $userId)->delete();
        FavoriteOrder::where('user_id', '=', $userId)->delete();
        FavoriteRestaurant::where('user_id', '=', $userId)->delete();
        FirebaseToken::where('user_id', '=', $userId)->delete();
        Order::where('user_id', '=', $userId)->delete();
        OrderFeedback::where('user_id', '=', $userId)->delete();
        RewardCoupon::where('user_id', '=', $userId)->delete();
        UserPreference::where('user_id', '=', $userId)->delete();
        UserRewards::where('user_id', '=', $userId)->delete();
        /*Email Templates*/
        $template = view('email-templates.account-deleted-admin')->render();
        sendEmail($template, $user['email'], 'Account Deleted: Falafel Corner');
        return response()->json(apiResponseHandler([], 'User Deleted Successfully', 200), 200);
    }

    public function deleteReward($rewardId,$type){
        if($type == 1){
            RewardCoupon::where('coupon_id', '=', $rewardId)->delete();
        }
        if($type == 2){
            BonusAppliedFor::where('id', '=', $rewardId)->delete();
        }
        return response()->json(apiResponseHandler([], 'Deleted Successfully', 200), 200);
    }

    public function editUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        User::where('id', '=', $request->input('id'))->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'mobile' => $request->input('mobile'),
            'zip_code' => $request->input('zip_code'),
            'email'=>$request->input('email'),
            'date_of_birth'=>strtotime($request->input('birthday'))
        ]);

        /*Email Templates*/
        $template = view('email-templates.edit-profile-admin',['name'=>$request->input('first_name'),'password'=>''])->render();
        sendEmail($template, $request->input('email'), 'Account Details Updated: Falafel Corner');

        return response()->json(apiResponseHandler([], 'User Details Updated Successfully', 200), 200);
    }

    public function resetUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/'
            ],
            'email' => 'required',
        ], ['password.regex' => 'Password must be 8 alphanumeric characters']);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $user = User::where('id', '=', $request->input('id'))->first();
        User::where('id', '=', $request->input('id'))->update([
            'password' => Hash::make($request->input('password'))
        ]);

        /*Email Templates*/
        $template = view('email-templates.edit-profile-admin',['name'=>$request->input('first_name'),'password'=>$request->input('password')])->render();
        sendEmail($template, $user['email'], 'Account Details Updated: Falafel Corner');

        return response()->json(apiResponseHandler([], 'Password updated.', 200), 200);
    }

    public function addUserRewards(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'reward_points' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $userReward = new UserRewards();
        $userReward->order_id = 0;
        $userReward->user_id = $request->input('id');
        $userReward->total_rewards = $request->input('reward_points');
        $userReward->type = 4;
        $userReward->month = strtotime(date('Y-m', time()) . '-1');
        $userReward->save();
        return response()->json(apiResponseHandler([], 'Reward points added successfully.', 200), 200);
    }
}
