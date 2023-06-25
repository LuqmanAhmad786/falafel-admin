<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssignAdminReward;
use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\Cart\CartList;
use App\Models\DevicePreference;
use App\Models\Favorite\FavoriteLabel;
use App\Models\Favorite\FavoriteOrder;
use App\Models\FavoriteRestaurant;
use App\Models\FirebaseToken;
use App\Models\ForgotPassword;
use App\Models\Order;
use App\Models\Order\OrderFeedback;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\SubscriptionPreference;
use App\Models\User\BillingAddress;
use App\Models\User\UserAddress;
use App\Models\User\UserMembership;
use App\Models\UserPreference;
use App\Models\UserRewards;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = Auth::user();/*$user['date_of_birth'] = date('m/d/Y', $user['date_of_birth'])*/;
        return response()->json(apiResponseHandler($user));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            /*'email' => ['required', Rule::unique('users')->ignore($user->id)],*/
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $user = User::find($user->id);
        if (!empty($user)) {
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->mobile = $request->input('mobile');
            $user->zip_code = $request->input('zip_code');
//            $user->date_of_birth = strtotime(str_replace('/', '-', $request->input('date_of_birth')));
            $user->save();

            /*Email Templates*/
            $template = view('email-templates.edit-profile', ['name' => $user->first_name])->render();
            sendEmail($template, $user->email, 'Account Details Updated: Falafel Corner');

            return response()->json(apiResponseHandler($user, 'Successfully updated the profile'));
        } else {
            return response()->json(apiResponseHandler([], 'User not found', 400), 400);
        }

    }

    public function selectPreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $isAlready = UserPreference::where('user_id', '=', Auth::user()->id)->get();

        if (count($isAlready)) {
            UserPreference::where('user_id', '=', Auth::user()->id)->update([
                'restaurant_id' => $request->input('restaurant_id')
            ]);
        } else {
            $userPreference = new UserPreference();
            $userPreference->user_id = Auth::user()->id;
            $userPreference->restaurant_id = $request->input('restaurant_id');
            $userPreference->save();
        }


        return response()->json(apiResponseHandler($request->input('restaurant_id'), 'Success', 200));
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $isUser = User::where('email', '=', $request->input('email'))->get();
        if (count($isUser)) {
            $setOtp = generateOtp();
            $forgotPassword = new ForgotPassword();
            $forgotPassword->email = $request->input('email');
            $forgotPassword->otp = $setOtp;
            $forgotPassword->expiry = 3600;
            $forgotPassword->save();

            /* $to = $request->input('email');
             $subject = "Forgot Password - OTP";
             $txt = "Your Otp  :  " . $setOtp;
             $headers = "From: ffk@qualwebs.com";
             mail($to, $subject, $txt, $headers);*/

            $link = "https://fcorner.com/reset-password/".Crypt::encryptString($request->input('email').'+'.$setOtp);

            $template = view('email-templates.forgot-password', ['name' => $isUser[0]->first_name, 'link' => $link])->render();
            sendEmailFalafel($template, $request->input('email'), 'Password Reset: Falafel Corner');

            return response()->json(apiResponseHandler([], 'Password reset link sent successfully on your email address. Please check for further.'), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Email address doesn\'t exist', 400), 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required',
            'password' => 'required',
            're_password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('password') != $request->input('re_password')) {
            return response()->json(apiResponseHandler([], "Password and Confirm password should match!", 400), 400);
        }

        $key = Crypt::decryptString($request->input('key'));

        $key = explode('+',$key);

        $isOtpCorrect = ForgotPassword::where('email', '=', $key[0])
            ->where('otp', '=', $key[1])->get();

        if (count($isOtpCorrect)) {
            User::where('email', '=', $key[0])->update([
                'password' => Hash::make($request->input('password'))
            ]);

            $user = User::where('email', '=', $key[0])->first();
            $template = view('email-templates.reset-password', ['name' => $user->first_name])->render();
            sendEmailFalafel($template, $key[0], 'Reset Password.');

            return response()->json(apiResponseHandler([], 'Password reset successfully. Please login to continue.'), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Something went wrong.', 400), 400);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $subscriptionPreference = SubscriptionPreference::where('user_id', '=', Auth::user()->id)->first();

        if (Auth::guard('web')->attempt(["id" => Auth::user()->id, "password" => $request->input('old_password')])) {
            User::where('id', '=', Auth::user()->id)->update(['password' => Hash::make($request->input('new_password'))]);

            /*Email Notification*/
            $template = view('email-templates.change-password', ['name' => Auth::user()->first_name])->render();

            if ($subscriptionPreference['email_subscription']) {
                sendEmail($template, Auth::user()->email, 'Password Updated Successfully: Falafel Corner');
            }

            /*Push Notification*/
            $devicePreference = DevicePreference::where('user_id', '=', Auth::user()->id)->first();
            $tokens = FirebaseToken::where('user_id', '=', Auth::user()->id)->get();
            $receivers = FirebaseToken::where('user_id', '=', Auth::user()->id)->get();
            $platform = FirebaseToken::where('user_id', '=', Auth::user()->id)->first();
            $notification = array(
                'message' => 'Hi,' . ' ' . Auth::user()->first_name .' , your FCorner password has been successfully updated.',
                'title' => 'Password Changed.',
                'body' => 'Hello,' . ' ' . Auth::user()->first_name . ' , your FCorner password has been successfully updated.',
                'type' => 2,
                'data' => (object)array(),
                'sound' => 'default'
            );

            if ($devicePreference['push_notification']) {
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'],$notification['body'],[$tk['token']],['notification_type'=>2]);
                }
            }
            app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);

            return response()->json(apiResponseHandler([], 'Password Changed successfully . '), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Old Password is not correct . Please Try Again', 400), 400);
        }
    }

    public function placeOrderFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required'
                /* Rule::exists('orders', 'order_id')->where(function ($query) {
                     return $query->where('user_id', ' = ', Auth::user()->id);
                 })*/
            ],
            'feedback' => ['required', Rule::in([1, 2])],
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if($request->input('feedback') == 2){
            $validator = Validator::make($request->all(), [
                'review' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
            }
        }

        OrderFeedback::create([
            'order_id' => $request->input('order_id'),
            'user_id' => Auth::user()->id,
            'feedback' => $request->input('feedback'),
            'review' => $request->input('review')
        ]);

        /*Email Notification*/
//        $template = view('email-templates.order-feedback', [
//            'name' => Auth::user()->first_name,
//        ])->render();
//
//        $subscriptionPreference = SubscriptionPreference::where('user_id', '=', Auth::user()->id)->first();
//        if ($subscriptionPreference['email_subscription']) {
//            sendEmail($template, Auth::user()->email, 'Order Feedback.');
//        }


        return response()->json(apiResponseHandler([], 'Feedback submitted successfully . '));
    }

    public function setRestaurantPreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_preference' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        User::where('id', '=', Auth::user()->id)->update([
            'restaurant_preference' => $request->input('restaurant_preference')
        ]);

        $response = (object)array();
        if ($request->input('restaurant_preference') == 1) {
            $response = Restaurant::select('restaurants.*')->selectRaw('MIN((6371 * acos(cos(radians(?)) *
                    cos(radians(latitude))
                    * cos(radians(longitude) - radians(?)
                ) + sin(radians(?)) *
            sin(radians(latitude)) )
                          )) AS distance',
                [Auth::user()->latitude, Auth::user()->longitude, Auth::user()->latitude])
                ->first();
        } elseif ($request->input('restaurant_preference') == 2) {
            $getOrders = Order\Order::where('user_id', '=', Auth::user()->id)->orderBy('created_at', 'DESC')->get();
            if (count($getOrders)) {
                $response = Restaurant::where('id', '=', $getOrders[0]['restaurant_id'])->first();
            } else {
                return response()->json(apiResponseHandler([], 'Sorry, you haven\'t placed any order yet.', 400), 400);
            }
        } elseif ($request->input('restaurant_preference') == 3) {
            $getFavorite = FavoriteRestaurant::where('user_id', '=', Auth::user()->id)->get();
            if (count($getFavorite)) {
                $response = Restaurant::where('id', '=', $getFavorite[0]['restaurant_id'])->first();
            } else {
                return response()->json(apiResponseHandler([], 'Sorry, you haven\'t any restaurant as favorite yet.', 400), 400);
            }
        }

        return response()->json(apiResponseHandler($response, 'Preference Set Successfully.'));
    }

    public function setUserAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        User::where('id', ' = ', Auth::user()->id)->update([
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude')
        ]);

        return response()->json(apiResponseHandler([], 'Success . '));
    }

    public function deleteMyAccount()
    {
        $email = Auth::user()->email;
        User::where('id', '=', Auth::user()->id)->update(['is_account_deleted' => 1]);
        AssignAdminReward::where('user_id', '=', Auth::user()->id)->delete();
        BonusAppliedFor::where('user_id', '=', Auth::user()->id)->delete();
        CartList::where('user_id', '=', Auth::user()->id)->delete();
        DevicePreference::where('user_id', '=', Auth::user()->id)->delete();
        FavoriteLabel::where('added_by', '=', Auth::user()->id)->delete();
        FavoriteOrder::where('user_id', '=', Auth::user()->id)->delete();
        FavoriteRestaurant::where('user_id', '=', Auth::user()->id)->delete();
        FirebaseToken::where('user_id', '=', Auth::user()->id)->delete();
        Order\Order::where('user_id', '=', Auth::user()->id)->delete();
        RewardCoupon::where('user_id', '=', Auth::user()->id)->delete();
        SubscriptionPreference::where('user_id', '=', Auth::user()->id)->delete();
        UserPreference::where('user_id', '=', Auth::user()->id)->delete();
        /*Email Templates*/
        $template = view('email-templates.account-deleted')->render();
        sendEmail($template, $email, 'Account Deleted: Falafel Corner');
        return response()->json(apiResponseHandler([], 'Account Deleted Successfully . '));
    }

    public function setSubscriptionPreference(Request $request)
    {
        SubscriptionPreference::where('user_id', '=', Auth::user()->id)->delete();
        SubscriptionPreference::create([
            'user_id' => Auth::user()->id,
            'email_subscription' => $request->input('email_subscription'),
            'phone_number_subscription' => $request->input('phone_number_subscription'),
        ]);
        return response()->json(apiResponseHandler([], 'Preference Set Successfully.'));
    }

    public function setDevicePreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'push_notification' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        DevicePreference::where('user_id', '=', Auth::user()->id)->delete();
        DevicePreference::create([
            'user_id' => Auth::user()->id,
            'push_notification' => $request->input('push_notification')
        ]);

        return response()->json(apiResponseHandler([], 'Preference Set Successfully.'));
    }

    public function getMyPreferences()
    {
        $resPreference = User::where('id', '=', Auth::user()->id)->first();
        $response['restaurant_preference'] = $resPreference['restaurant_preference'];
        $response['subscription_preference'] = SubscriptionPreference::where('user_id', '=', Auth::user()->id)
            ->select('email_subscription', 'phone_number_subscription')
            ->first();
        $response['device_preference'] = DevicePreference::where('user_id', '=', Auth::user()->id)
            ->select('push_notification')
            ->first();
        return response()->json(apiResponseHandler($response, 'success'));
    }

    public function doLogout()
    {
        $user = FirebaseToken::where('user_id', '=', Auth::user()->id)->delete();
        return response()->json(apiResponseHandler([], 'Logout Successfully.', 200), 200);
    }

    public function addFavoriteLabel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label_name' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $favoriteLabel = new FavoriteLabel();
        $favoriteLabel->label_name = $request->input('label_name');
        $favoriteLabel->added_by = Auth::user()->id;
        $favoriteLabel->save();

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
            $favoriteOrder->favorite_label_id = $favoriteLabel->favorite_label_id;
            $favoriteOrder->save();
            return response()->json(apiResponseHandler([], 'Marked as favorite', 200), 200);
        }
    }

    public function getBonus()
    {
        $response = BonusAppliedFor::leftJoin('bonus', 'bonus.bonus_id', 'bonus_applied_for.bonus_id')
            ->leftJoin('items', 'items.item_id', 'bonus.bonus_free_item_id')
            ->where('user_id', '=', Auth::user()->id)
            ->where('is_used', '=', 0)
            ->where('bonus_type','!=','1')
            ->select(
                'bonus_applied_for.bonus_id',
                'bonus_applied_for.user_id',
                'bonus_applied_for.is_used',
                'bonus.bonus_type',
                'bonus.bonus_condition_type',
                'bonus.bonus_name',
                'bonus.bonus_expiry',
                DB::RAW('ROUND(UNIX_TIMESTAMP(bonus.bonus_expiry)) AS bonus_expiry_unix'),
                'bonus.notification_text',
                'bonus.description',
                'bonus.term_and_condition',
                'bonus.bonus_free_item_id',
                'bonus.bonus_extra_point',
                'bonus.bonus_points_multiplier',
                'bonus.bonus_discount',
                'bonus.bonus_orders_no',
                'bonus.bonus_start_date',
                'bonus.bonus_end_date',
                'bonus.bonus_plates_no',
                'bonus.bonus_user_points',
                'items.item_name',
                'items.item_price',
                'items.item_image',
                'items.item_description',
                'items.restaurant_id'
            )
            ->orderBy('bonus.bonus_expiry','desc')
            ->get();

        return response()->json(apiResponseHandler($response, 'Success.'));
    }

    public function contactUs(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'contact'=>'required',
            'message'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $message = '<p>Hello,<p/>';
        $message .= '<p>Received a new inquiry on Falafel Corner.</p>';
        $message .= '<p>Name: '.$request->input('name').'</p>';
        $message .= '<p>Email: '.$request->input('email').'</p>';
        $message .= '<p>Phone Number: '.$request->input('contact').'</p>';
        $message .= '<p>Message: '.$request->input('message').'</p>';
        $message .= '<p>Thank You</p>';

        sendEmailFalafel($message,'franchising@fcorner.com','New inquiry on Falafel Corner');
        sendEmailFalafel($message,'info@fcorner.com','New inquiry on Falafel Corner');
        return response()->json(apiResponseHandler([], 'Thank you for Inquiry', 200), 200);
    }

    public function getUserAddresses(){
        $addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        return response()->json(apiResponseHandler($addresses, '', 200), 200);
    }

    public function storeUserAddress(Request $request){
        $validator = Validator::make($request->all(), [
            'address_line_1' => 'required',
            'contact_person' => 'required',
            'contact_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $id = $request->input('id');
        $data = [
            'user_id' => Auth::user()->id,
            'address_line_1' => $request->input('address_line_1'),
            'contact_person' => $request->input('contact_person'),
            'contact_number' => $request->input('contact_number'),
            'order_note' => $request->input('order_note'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'is_default' => $request->input('is_default'),
        ];
        $address = UserAddress::updateOrCreate(['id' => $id],$data);

        return response()->json(apiResponseHandler([], '', 200), 200);
    }

    public function getUserAddress($id){
        $addresses = UserAddress::where('user_id', Auth::user()->id)->where('id', $id)->first();
        return response()->json(apiResponseHandler($addresses, '', 200), 200);
    }

    public function deleteUserAddress($id){
        $addresses = UserAddress::where('user_id', Auth::user()->id)->where('id', $id)->delete();
        return response()->json(apiResponseHandler([], '', 200), 200);
    }

    public function setAddressDefault($id){
        UserAddress::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
        UserAddress::where('user_id', Auth::user()->id)->where('id', $id)->update(['is_default' => 1]);
        return response()->json(apiResponseHandler([], '', 200), 200);
    }

    public function getAllBillingAddresses(){
        $addresses = BillingAddress::where('user_id', Auth::user()->id)->get();
        return response()->json(apiResponseHandler($addresses, '', 200), 200);
    }

    public function userMembership(){
        $data = UserMembership::with(['membership'])
                ->where('user_id',Auth::user()->id)
                ->first();
        return response()->json(apiResponseHandler($data, '', 200), 200);
    }
}
