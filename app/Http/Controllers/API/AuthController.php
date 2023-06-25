<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BonusAppliedFor;
use App\Models\Card;
use App\Models\Cart\CartList;
use App\Models\DevicePreference;
use App\Models\FirebaseToken;
use App\Models\ManageNotifications;
use App\Models\Order;
use App\Models\SubscriptionPreference;
use App\Models\User\UserCard;
use App\Models\User\UserMembership;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first()), 400);
        }

        if ($request->filled('token')) {
            FirebaseToken::where([
                'token' => $request->input('token')
            ])->delete();
        }

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_server_user' => 0
        ];

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_account_deleted) {
                return response()->json(apiResponseHandler([], 'Your account has been deleted.', 400), 400);
            } else {
                $user = User::where(['email' => $request->input('email')])->first();
                User::where('id',$user->id)->update([
                    'last_login' => time()
                ]);
                $token = $user->createToken('myApp')->accessToken;
                $user = Auth::user();
//                $user['date_of_birth'] = date('m-d-Y', $user['date_of_birth']);
                $user['token'] = $token;

                if ($request->input('cart_id')) {
                    CartList::where('cart_id', '=', $request->get('cart_id'))->update([
                        'user_id' => Auth::user()->id
                    ]);
                }

                if ($request->filled('token') && $request->filled('platform')) {
                    FirebaseToken::where('user_id', Auth::user()->id)->delete();
                    FirebaseToken::create([
                        'user_id' => Auth::user()->id,
                        'token' => $request->input('token'),
                        'platform' => $request->input('platform')
                    ]);
                }
                return response()->json(apiResponseHandler($user, 'Logged in Successfully.'));
            }
        } else {
            return response()->json(apiResponseHandler([], 'Wrong Credentials', 400), 400);
        }
    }

    public function serverLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first()), 400);
        }

        if ($request->filled('token')) {
            FirebaseToken::where([
                'token' => $request->input('token')
            ])->delete();
        }

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_server_user' => 1,
            'is_account_deleted' => 0
        ];

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_account_deleted) {
                return response()->json(apiResponseHandler([], 'Your account has been deleted.', 400), 400);
            } else {
                $user = User::where(['email' => $request->input('email')])->first();
                User::where('id',$user->id)->update([
                    'last_login' => time()
                ]);
                $token = $user->createToken('myApp')->accessToken;
                $user = Auth::user();
                $user['token'] = $token;
                return response()->json(apiResponseHandler($user, 'Logged in Successfully.'));
            }
        } else {
            return response()->json(apiResponseHandler([], 'Wrong Credentials', 400), 400);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
            'mobile' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $isDeleteExist = User::where('email', '=', $request->input('email'))
            ->where('is_account_deleted', '=', 1)
            ->get();

        if (count($isDeleteExist)) {
            User::where('email', '=', $request->input('email'))->delete();
        }

        if ($request->filled('token')) {
            FirebaseToken::where([
                'token' => $request->input('token')
            ])->delete();
        }

        $user = new User();
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->mobile = $request->input('mobile');
        $user->password = Hash::make($request->input('password'));
        $user->customer_id = 'cus_' . generateCustomerId();
        if ($request->exists('zip_code') && $request->has('zip_code')) {
            $user->zip_code = $request->input('zip_code');
        }
        /*if ($request->exists('date_of_birth') && $request->has('date_of_birth')) {
            $user->date_of_birth = strtotime(str_replace('/', '-', $request->input('date_of_birth')));
        }*/
//        $user->date_of_birth = $request->input('date_of_birth');
        if ($request->filled('google_id')) {
            $user->google_id = $request->input('google_id');
        }
        if ($request->filled('facebook_id')) {
            $user->facebook_id = $request->input('facebook_id');
        }
        $user->save();

        //update guest orders with same email used for register
        Order::where('user_email', '=', $request->input('email'))->update(['user_id' => $user->id]);

        $token = $user->createToken('myApp')->accessToken;

//        $user['date_of_birth'] = date('m-d-Y', $user['date_of_birth']);
        $user['token'] = $token;

        if ($request->input('cart_id')) {
            CartList::where('cart_id', '=', $request->get('cart_id'))->update([
                'user_id' => $user->id
            ]);
        }

        if ($request->filled('token') && $request->filled('platform')) {
            FirebaseToken::create([
                'user_id' => $user->id,
                'token' => $request->input('token'),
                'platform' => $request->input('platform')
            ]);

            //old notification message
            // 'Your FCorner account is all set. You can now order online and earn special reward points!'
            // old notification message
            sendCloudNotification(
                'Account Created',
                'Your Falafel Corner account is all set. You can now order online and earn special reward points!',
                [$request->input('token')],
                []
            );
        }

        // DEFAULT NOTIFICATION ON
        $devicePref = new DevicePreference;
        $devicePref->user_id = $user->id;
        $devicePref->push_notification = 1;
        $devicePref->save();

        // DEFAULT EMAIL/NUMBER SUBSCRIPTION
        $subscription = new SubscriptionPreference;
        $subscription->user_id = $user->id;
        $subscription->email_subscription = 1;
        $subscription->phone_number_subscription = 1;
        $subscription->save();

        $card = Card::find(1001);
        if($card){
            UserCard::create([
                'user_id' => $user->id,
                'unique_id' => Uuid::uuid4(),
                'card_number' => round(microtime(true)*1000),
                'gift_card_id' => 1001,
                'balance' => 0,
                'card_nickname' => $card->card_name,
                'non_deletable' => 1,
            ]);
        }

        // CREATE BASIC MEMBERSHIP
        $membership = new UserMembership();
        $membership->user_id = $user->id;
        $membership->membership_id = 1;
        $membership->membership_expiry = Carbon::today()->addYears(25)->format('Y-m-d');
        $membership->save();

        /*Email Templates*/
        $template = view('email-templates.registration', ['name' => $user->first_name])->render();
        sendEmail($template, $user->email, 'Welcome to Falafel Corner');

        return response()->json(apiResponseHandler($user, 'Success'));
    }

    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first()), 400);
        }

        $user = User::where(['google_id' => $request->input('google_id')])->count();
        if ($user == 0) {
            return response()->json(apiResponseHandler([], "You\'re almost there! We just need a few more details."), 400);
        }

        $user = User::where(['google_id' => $request->input('google_id')])->first();

        if ($user->is_account_deleted) {
            User::where('google_id', '=', $request->input('google_id'))->delete();
            return response()->json(apiResponseHandler([], 'Your account has been deleted.', 400), 400);
        }

        $token = $user->createToken('myApp')->accessToken;
        /*$user['date_of_birth'] = date('m-d-Y', $user['date_of_birth']);*/
        $user['token'] = $token;

        if ($request->filled('cart_id')) {
            CartList::where('cart_id', '=', $request->input('cart_id'))->update([
                'user_id' => $user->id
            ]);
        }

        if ($request->filled('token') && $request->filled('platform')) {
            FirebaseToken::where([
                'token' => $request->input('token')
            ])->delete();

            FirebaseToken::create([
                'user_id' => $user->id,
                'token' => $request->input('token'),
                'platform' => $request->input('platform')
            ]);
        }
        return response()->json(apiResponseHandler($user, ''));
    }

    public function facebookLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first()), 400);
        }

        $user = User::where(['facebook_id' => $request->input('facebook_id')])->count();

        if ($user == 0) {
            return response()->json(apiResponseHandler([], "You\'re almost there! We just need a few more details."), 400);
        }

        $user = User::where(['facebook_id' => $request->input('facebook_id')])->first();

        if ($user->is_account_deleted) {
            User::where('google_id', '=', $request->input('google_id'))->delete();
            return response()->json(apiResponseHandler([], 'Your account has been deleted.', 400), 400);
        }

        $token = $user->createToken('myApp')->accessToken;
        /*$user['date_of_birth'] = date('m-d-Y', $user['date_of_birth']);*/
        $user['token'] = $token;

        if ($request->filled('cart_id')) {
            CartList::where('cart_id', '=', $request->get('cart_id'))->update([
                'user_id' => $user->id
            ]);
        }

        if ($request->filled('token') && $request->filled('platform')) {
            FirebaseToken::where([
                'token' => $request->input('token')
            ])->delete();

            FirebaseToken::create([
                'user_id' => $user->id,
                'token' => $request->input('token'),
                'platform' => $request->input('platform')
            ]);
        }

        return response()->json(apiResponseHandler($user, ''));
    }

    public function storeFireBaseToken($parameters)
    {
        FirebaseToken::where('token', '=', $parameters['token'])->delete();
        FirebaseToken::create([
            'user_id' => Auth::user()->id,
            'token' => $parameters['token'],
            'platform' => $parameters['platform'],
            'type' => 1,
        ]);
    }
}
