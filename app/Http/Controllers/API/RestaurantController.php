<?php

namespace App\Http\Controllers\API;

use App\Models\Menu;
use App\Models\Restaurant\RestaurantMenuTiming;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function getRestaurantTimings(Request $request){
        $resId = $request->get('res_id');
        $menu = Menu::where('restaurant_id',$resId)->first();

        $restaurantTimings = RestaurantMenuTiming::where('restaurant_menu_id',$menu->menu_id)->get();

        $i = 0;
        foreach ($restaurantTimings AS $restaurantTiming){
            $restaurantTimings[$i]->from_1 = strtotime($restaurantTiming->from_1);
            $restaurantTimings[$i]->to_1 = strtotime($restaurantTiming->to_1);
            if($restaurantTiming->from_2 && $restaurantTiming->to_2){
                $restaurantTimings[$i]->from_2 = strtotime($restaurantTiming->from_2);
                $restaurantTimings[$i]->to_2 = strtotime($restaurantTiming->to_2);
            }
            $i++;
        }

        return response()->json(apiResponseHandler($restaurantTimings,'',200), 200);
    }

    public function franchiseInquiry(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'city' => 'required',
            'state' => 'required',
            'about' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $city = $request->input('city');
        $state = $request->input('state');
        $about = $request->input('about');

        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'city' => $city,
            'state' => $state,
            'about' => $about,
        ];

        $template = view('email-templates.franchise-inquiry',$data)->render();
        sendEmailFalafel($template,'kapil@qualwebs.com','New Franchise Inquiry',$email);
        sendEmailFalafel($template,'franchising@fcorner.com','New Franchise Inquiry',$email);
        sendEmailFalafel($template,'info@fcorner.com','New Franchise Inquiry',$email);

        return response()->json(apiResponseHandler([], '', 200), 200);
    }
}
