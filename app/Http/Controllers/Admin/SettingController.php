<?php

namespace App\Http\Controllers\Admin;

use App\GlobalSettings;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StripeController;
use App\Models\Favorite\FavoriteLabel;
use App\Models\ManageNotifications;
use App\Models\Menu;
use App\Models\PaginationLimit;
use App\Models\Restaurant;
use App\Models\Restaurant\CloverToken;
use App\Models\StripeBankAccount;
use App\Models\Timing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Restaurant\RestaurantMenuTiming;
use App\Models\RestaurantOfflineDate;
use Stripe\Stripe;

class SettingController extends Controller
{
    public function timingPage()
    {
        $lunch = Timing::where(['name' => 'LUNCH'])->select(
            DB::raw('TIME_FORMAT(timings.from, "%H") as from_hour'),
            DB::raw('TIME_FORMAT(timings.to, "%H") as to_hour'),
            DB::raw('TIME_FORMAT(timings.from, "%i") as from_minutes'),
            DB::raw('TIME_FORMAT(timings.to, "%i") as to_minutes')
        )->first();

        $breakfast = Timing::where(['name' => 'BREAKFAST'])->select(
            DB::raw('TIME_FORMAT(timings.from, "%H") as from_hour'),
            DB::raw('TIME_FORMAT(timings.to, "%H") as to_hour'),
            DB::raw('TIME_FORMAT(timings.from, "%i") as from_minutes'),
            DB::raw('TIME_FORMAT(timings.to, "%i") as to_minutes')
        )->first();

        return view('settings.timing', ['lunch' => $lunch, 'breakfast' => $breakfast]);
    }

    public function restaurantsPage()
    {
        $restaurants = Restaurant::with('bankAccount')->get();

        return view('settings.restaurants', ['restaurants' => $restaurants]);
    }

    public function updateTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_hour' => 'required',
            'from_minutes' => 'required',
            'to_hour' => 'required',
            'to_minutes' => 'required',
            'type' => ['required', Rule::in([1, 2])]
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $from = sprintf("%02d", $request->input('from_hour')) . sprintf("%02d", $request->input('from_minutes')) . '00';
        $to = sprintf("%02d", $request->input('to_hour')) . sprintf("%02d", $request->input('to_minutes')) . '00';

        $name = $request->input('type') == 1 ? 'BREAKFAST' : 'LUNCH';

        Timing::updateOrCreate([
            'name' => $name
        ], [
            'from' => $from,
            'to' => $to
        ]);

        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function favoriteNamePage()
    {
        $allLabel = FavoriteLabel::get();
        return view('settings.favorite-name', ['all_label' => $allLabel]);
    }

    public function addFavoriteLabel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $favoriteLabel = new FavoriteLabel();
        $favoriteLabel->label_name = $request->input('label_name');
        $favoriteLabel->added_by = 0;
        $favoriteLabel->save();
        return response()->json(apiResponseHandler([$favoriteLabel], 'New favorite label added Successfully', 200), 200);
    }


    public function deleteFavoriteLabel($labelId)
    {
        if ($labelId) {
            FavoriteLabel::where('favorite_label_id', '=', $labelId)->delete();
            return response()->json(apiResponseHandler([], 'Label Deleted Successfully', 200), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Not Found', 400), 400);
        }
    }

    public function notificationsPage()
    {
        $allNotifications = ManageNotifications::get();
        return view('settings.manage-notifications', ['list' => $allNotifications]);
    }

    public function setNotificationText(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required',
            'type_name' => 'required',
            'message_text' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('id')) {
            ManageNotifications::where('type_id', '=', $request->input('type_id'))->update($request->all());
        } else {
            $alreadyExist = ManageNotifications::where('type_id', '=', $request->input('type_id'))->get();
            if (count($alreadyExist)) {
                return response()->json(apiResponseHandler([], 'Already added.', 400), 400);
            } else {
                $notification = new ManageNotifications();
                $notification->type_id = $request->input('type_id');
                $notification->type_name = $request->input('type_name');
                $notification->message_text = $request->input('message_text');
                $notification->save();
                return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
            }
        }
    }

    public function deleteNotifications($rowId)
    {
        if ($rowId) {
            ManageNotifications::where('id', '=', $rowId)->delete();
            return response()->json(apiResponseHandler([], 'Deleted Successfully', 200), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Not Found', 400), 400);
        }
    }

    public function loadPaginationLimit()
    {
        $pagination = PaginationLimit::get();
        return view('settings.pagination-limit', ['list' => $pagination]);
    }

    public function setPaginationLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        PaginationLimit::truncate();
        PaginationLimit::create($request->all());
        return response()->json(apiResponseHandler([], 'Successfully', 200), 200);
    }

    public function restaurantStatus($restaurantId, $isOpened)
    {
        Restaurant::where('id', '=', $restaurantId)->update(['is_opened' => $isOpened]);
        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function loadGlobalSettings()
    {
        $data = GlobalSettings::first();
        return view('dashboard.tax-settings', ['data' => $data]);
    }

    public function updateGlobalSettings(Request $request)
    {
        GlobalSettings::where('id', 1)->update([
            'tax_value' => $request->input('tax_value'),
            'pickup_notification_time' => $request->input('pickup_notification_time'),
            'feedback_notification_time' => $request->input('feedback_notification_time')
        ]);
        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function loadTimingView()
    {
        $resId = Session::get('my_restaurant');
        $restaurant = Restaurant::where('id', $resId)->first();
        $menu = Menu::where('restaurant_id', $resId)->first();
        $breakfastTimings = RestaurantMenuTiming::where('restaurant_menu_id', $menu->menu_id)->get();
        $offlineDates = RestaurantOfflineDate::all();
        return view('dashboard.settings.timings', [
            'restaurant' => $restaurant,
            'breakfastTimings' => $breakfastTimings,
            'offlineDates' => $offlineDates
        ]);
    }

    public function updateMenuTimingRes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'from_1' => 'required',
            'to_1' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        RestaurantMenuTiming::where('id', $request->input('id'))
            ->update([
                'from_1' => $request->input('from_1'),
                'to_1' => $request->input('to_1'),
                'from_2' => $request->input('from_2'),
                'to_2' => $request->input('to_2'),
                'offline' => $request->input('offline'),
            ]);
        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function updateOfflineDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'required',
            'endDate' => 'required',
            'offline_message_range' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }
        $resId = Session::get('my_restaurant');
        RestaurantOfflineDate::create([
            'restaurant_id' => $resId,
            'start_date' => $request->input('startDate'),
            'end_date' => $request->input('endDate'),
            'offline_message' => $request->input('offline_message_range'),
        ]);
        return response()->json(apiResponseHandler([], '', 200), 200);

    }

    public function updateOfflineDatesDelete($id)
    {
        RestaurantOfflineDate::where('id', $id)->delete();
        return Redirect::back();
    }

    public function getBankDetails($id){
        $restaurant = Restaurant::with('bankAccount')->where('id',$id)->first();
        if($restaurant && $restaurant->bankAccount){
            return app('App\Http\Controllers\StripeController')->retrieveBankInfo($restaurant);
        } else{
            if(!$restaurant){
                return response()->json(apiResponseHandler([], 'Invalid restaurant selected.', 400), 400);
            } else{
                return response()->json(apiResponseHandler([], 'Bank details not found for this restaurant.', 400), 400);
            }
        }
    }

    public function saveBankDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'account_holder_name' => 'required',
            'account_number' => 'required',
            'routing_number' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $stripeAccount = StripeBankAccount::where('restaurant_id',$request->input('restaurant_id'))->first();
        return app('App\Http\Controllers\StripeController')->createStripeAccount($request);
    }

    /**
     * @return mixed
     */
    public function stripeKey(){
        $stripe = Config::get('stripe');
        $key = $stripe['live']['secret_key'];
        return $key;
    }

    public function saveCloverAppData(Request $request){
        $token = $request->input('auth_token');
        $mid = $request->input('merchant_id');
        $appId = $request->input('app_id');

        $token = Crypt::encryptString($token);

        CloverToken::updateOrCreate([
            'app_id' => $appId,
            'merchant_id' => $mid
        ],[
            'auth_token' => $token
        ]);
        return response()->json(apiResponseHandler([], 'Success', 200),200);
    }
}
