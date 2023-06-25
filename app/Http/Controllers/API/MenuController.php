<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\Category;
use App\Models\CompleteMeals;
use App\Models\Favorite\FavoriteItem;
use App\Models\FavoriteRestaurant;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\ModifierGroup;
use App\Models\ModifierGroupRelations;
use App\Models\ModifierItems;
use App\Models\Order;
use App\Models\Order\OrderDetail;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function getMenuType($restaurantId)
    {
        $response = Menu::where('restaurant_id', '=', $restaurantId)->get();
        foreach ($response as $item) {
            $item->from = strtotime($item->from);
            $item->slot_start = time();
            $item->to = strtotime($item->to);
        }
        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function getCategories($restaurantId){
        $response = Category::where('restaurant_id',$restaurantId)->limit(8)->get();
        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function singleMenuType($menuId)
    {
        $response = Menu::where('menu_id', '=', $menuId)->first();
        $response['from'] = strtotime($response['from']);
        $response['to'] = strtotime($response['to']);
        $currentDayName = date('l');
        $message = null;

        if ($currentDayName == 'Saturday' && $response['to'] < time() && $response['menu_name'] == 'Breakfast') {
            $message = $response['menu_saturday_message'];
        }
        elseif ($currentDayName == 'Sunday' && $response['menu_name'] == 'Breakfast') {
            $message = $response['menu_resume_time_message'];
        }
        else {
            $message = $response['menu_resume_time_message'];
        }

        if ($response['to'] < time()) {
            return response()->json(apiResponseHandler($response, $message, 200), 200);
        } else {
            return response()->json(apiResponseHandler($response, '', 200));
        }
    }

    public function getMenu($typeId)
    {
        $restaurantId = Menu::where('menu_id', '=', $typeId)->first()->restaurant_id;
        $singleMenu = Menu::where('menu_id', '=', $typeId)->first();
        $endTime = strtotime($singleMenu['to']);
        $currentDayName = date('l');
        $message = '';

        if ($currentDayName == 'Saturday' && $endTime < time() &&  $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_saturday_message'];
        }
        elseif ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_resume_time_message'];
        }
        else {
            $message = $singleMenu['menu_resume_time_message'];
        }
        $response = MenuCategory::query();
        $subSelect = ['item_categories.item_id', 'item_categories.category_id', 'items.item_name',
            'items.item_price', 'items.item_description', 'items.item_image','items.is_common', DB::raw('COUNT(modifier_group_relations.item_id) AS modifiers')];

        $response->with([
            'menus' => function ($query) use ($restaurantId, $subSelect) {
                $query
                    ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                    ->leftJoin('rewards_items', 'rewards_items.item_id', 'items.item_id')
                    ->leftJoin('modifier_group_relations', 'modifier_group_relations.item_id', '=', 'items.item_id')
                    ->where('items.restaurant_id', '=', $restaurantId)
                    ->where('items.is_in_stock', '=', 1)
                    ->whereNotNull('items.item_id')
                    ->select($subSelect)
                    ->groupBy('items.item_id')
                    ->orderBy('items.order_no', 'ASC');
            }])->where('menu_id', '=', $typeId)
            ->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')
            ->leftJoin('item_categories', 'item_categories.category_id', 'menu_categories.category_id')
            ->whereNotNull('item_categories.category_id')
            ->whereNotNull('categories.category_id')
            ->groupBy('menu_categories.category_id')
            ->select('menu_categories.category_id', 'menu_categories.menu_id', 'categories.category_name', 'menu_categories.order_no');

        $response = $response->orderBy('menu_categories.order_no', 'ASC')->limit(8)->get();

        if ($endTime < time()) {
            return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]), 200);
        } else {
            if ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast'){
                return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]),200);
            }
            return response()->json(apiResponseHandler($response, '', 200, [$endTime, time()]),200);
        }
    }

    public function getFavoriteMenu($typeId)
    {
        $userId = Auth::user()->id;

        $favoriteItems = FavoriteItem::where('user_id',$userId)->pluck('item_id');

        $restaurantId = Menu::where('menu_id', '=', $typeId)->first()->restaurant_id;
        $singleMenu = Menu::where('menu_id', '=', $typeId)->first();
        $endTime = strtotime($singleMenu['to']);
        $currentDayName = date('l');
        $message = '';

        if ($currentDayName == 'Saturday' && $endTime < time() &&  $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_saturday_message'];
        }
        elseif ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_resume_time_message'];
        }
        else {
            $message = $singleMenu['menu_resume_time_message'];
        }
        $response = MenuCategory::query();
        $subSelect = ['item_categories.item_id', 'item_categories.category_id', 'items.item_name',
            'items.item_price', 'items.item_description', 'items.item_image','items.is_common', DB::raw('COUNT(modifier_group_relations.item_id) AS modifiers')];

        $response->with([
            'menus' => function ($query) use ($restaurantId, $subSelect,$favoriteItems) {
                $query
                    ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                    ->leftJoin('rewards_items', 'rewards_items.item_id', 'items.item_id')
                    ->leftJoin('modifier_group_relations', 'modifier_group_relations.item_id', '=', 'items.item_id')
                    ->where('items.restaurant_id', '=', $restaurantId)
                    ->where('items.is_in_stock', '=', 1)
                    ->whereIn('items.item_id', $favoriteItems)
                    ->whereNotNull('items.item_id')
                    ->select($subSelect)
                    ->groupBy('items.item_id')
                    ->orderBy('items.order_no', 'ASC');
            }])->where('menu_id', '=', $typeId)
            ->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')
            ->leftJoin('item_categories', 'item_categories.category_id', 'menu_categories.category_id')
            ->whereNotNull('item_categories.category_id')
            ->whereNotNull('categories.category_id')
            ->groupBy('menu_categories.category_id')
            ->select('menu_categories.category_id', 'menu_categories.menu_id', 'categories.category_name', 'menu_categories.order_no');

        $response = $response->orderBy('menu_categories.order_no', 'ASC')->limit(8)->get();

        if ($endTime < time()) {
            return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]), 200);
        } else {
            if ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast'){
                return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]),200);
            }
            return response()->json(apiResponseHandler($response, '', 200, [$endTime, time()]),200);
        }
    }

    public function getMenuByCategory($typeId,$categoryId)
    {
        $restaurantId = Menu::where('menu_id', '=', $typeId)->first()->restaurant_id;
        $singleMenu = Menu::where('menu_id', '=', $typeId)->first();
        $endTime = strtotime($singleMenu['to']);
        $currentDayName = date('l');
        $message = '';

        if ($currentDayName == 'Saturday' && $endTime < time() &&  $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_saturday_message'];
        }
        elseif ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_resume_time_message'];
        }
        else {
            $message = $singleMenu['menu_resume_time_message'];
        }
        $response = MenuCategory::query();
        $subSelect = ['item_categories.item_id', 'item_categories.category_id', 'items.item_name',
            'items.item_price', 'items.item_description', 'items.item_image','items.is_common', DB::raw('COUNT(modifier_group_relations.item_id) AS modifiers')];

        $response->with([
            'menus' => function ($query) use ($restaurantId, $subSelect) {
                $query
                    ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                    ->leftJoin('rewards_items', 'rewards_items.item_id', 'items.item_id')
                    ->leftJoin('modifier_group_relations', 'modifier_group_relations.item_id', '=', 'items.item_id')
                    ->where('items.restaurant_id', '=', $restaurantId)
                    ->where('items.is_in_stock', '=', 1)
                    ->whereNotNull('items.item_id')
                    ->select($subSelect)
                    ->groupBy('items.item_id')
                    ->orderBy('items.order_no', 'ASC');
            }])->where('menu_id', '=', $typeId)
            ->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')
            ->leftJoin('item_categories', 'item_categories.category_id', 'menu_categories.category_id')
            ->whereNotNull('item_categories.category_id')
            ->whereNotNull('categories.category_id')
            ->where('categories.category_id',$categoryId)
            ->groupBy('menu_categories.category_id')
            ->select('menu_categories.category_id', 'menu_categories.menu_id', 'categories.category_name', 'menu_categories.order_no');

        $response = $response->orderBy('menu_categories.category_id', 'ASC')->limit(8)->get();

        if ($endTime < time()) {
            return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]), 200);
        } else {
            if ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast'){
                return response()->json(apiResponseHandler($response, $message, 200, [$endTime, time()]),200);
            }
            return response()->json(apiResponseHandler($response, '', 200, [$endTime, time()]),200);
        }
    }

    public function getRestaurants(Request $request)
    {
        if ($request->user('api')) {
            $userId = $request->user('api')->id;
            $response = Restaurant::with(['favorite' => function ($query) use ($userId) {
                $query->where('user_id', '=', $userId)
                    ->select('favorite_restaurants.restaurant_id',
                        DB::raw('(CASE WHEN favorite_restaurants.restaurant_id is NULL THEN 0 ELSE 1 END) as is_favorite')
                    )
                    ->get();
            }])->where('status',1)->get();
            return response()->json(apiResponseHandler($response));
        } else {
            $response = Restaurant::where('status',1)->get();
            return response()->json(apiResponseHandler($response));
        }
    }

    public function getRestaurantsDistance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $distance = DB::raw('3961 * 2 * ASIN(SQRT(POWER(SIN((latitude - abs(' . $request->input('latitude') . ')) * pi()/180 / 2),2) + COS(latitude * pi()/180 ) * COS(abs(' . $request->input('latitude') . ') * pi()/180) * POWER(SIN((longitude - ' . $request->input('longitude') . ') * pi()/180 / 2), 2) )) as distance');

        if ($request->user('api')) {
            $userId = $request->user('api')->id;
            $response = Restaurant::with(['favorite' => function ($query) use ($userId) {
                $query->where('user_id', '=', $userId)
                    ->select('favorite_restaurants.restaurant_id',
                        DB::raw('(CASE WHEN favorite_restaurants.restaurant_id is NULL THEN 0 ELSE 1 END) as is_favorite')
                    )
                    ->get();
            }, 'menu.timings'])->where('status',1)->select('restaurants.*', $distance);
        }else{
            $response = Restaurant::with(['menu.timings'])->where('restaurants.status',1)->select('restaurants.*', $distance);
        }
        $response = $response->orderBy('distance', 'ASC')->get();
        foreach ($response AS $restaurant){
            $timings = 'Timings | ';

            if($restaurant->menu){
                if(count($restaurant->menu->timings)){
                    $times = $restaurant->menu->timings;
                    foreach ($times AS $time){
                        $day = ucwords(substr($time->day,0,3));
                        $timings .=  $day.': '.date('gA', strtotime($time->from_1)) . '-'.date('gA', strtotime($time->to_1)).', ';
                    }
                }
            }

            $restaurant['timings'] = rtrim($timings,', ');
        }
        return response()->json(apiResponseHandler($response));
    }

    public function getInboundRestaurants(Request $request){
        $north = $request->input('ne_lat');
        $south = $request->input('sw_lat');
        $east = $request->input('ne_lng');
        $west = $request->input('sw_lng');
        $sql = "(latitude BETWEEN $south AND $north) AND
                (($west < $east AND longitude BETWEEN $west AND $east) OR
                ($west > $east AND (longitude BETWEEN $west AND 180 OR longitude BETWEEN -180 AND $east)))";
        //$response = DB::select($sql);

        if ($request->user('api')) {
            $userId = $request->user('api')->id;
            $response = Restaurant::with(['favorite' => function ($query) use ($userId) {
                $query->where('user_id', '=', $userId)
                    ->select('favorite_restaurants.restaurant_id',
                        DB::raw('(CASE WHEN favorite_restaurants.restaurant_id is NULL THEN 0 ELSE 1 END) as is_favorite')
                    )
                    ->get();
            }])->whereRaw($sql)->where('restaurants.status',1)->get();
        } else {
            $response = Restaurant::whereRaw($sql)->where('status',1)->get();
        }

        return response()->json(apiResponseHandler($response));
    }

    public function getTimezone()
    {
        $ip = Request::ip();
        $localIp = ['::1', '127.0.0.1'];

        if (!in_array($ip, $localIp)) {
            $response = json_decode(file_get_contents('http://ip-api.com/json/' . $ip), true);
            return $response['timezone'];
        } else {
            $response = json_decode(file_get_contents('http://ip-api.com/json/27.5.45.18'), true);
            return $response['timezone'];
        }
    }

    public function getMenuModifiers($menuId)
    {
        $modifierGroupIds = ModifierGroupRelations::where('item_id',$menuId)->pluck('modifier_group_id');
//        $modifier = ModifierItems::leftJoin('modifier_groups',
//            'modifier_groups.modifier_group_id', 'modifier_items.modifier_group_id')
//            ->whereIn('modifier_groups.modifier_group_id', $modifierGroupIds)
//            ->orderBy('modifier_groups.order_no', 'ASC')
//            ->get();

        $modifier = ModifierGroup::with('items')
            ->whereIn('modifier_groups.modifier_group_id', $modifierGroupIds)
            ->orderBy('modifier_groups.order_no', 'ASC')
            ->get();


        foreach ($modifier as $key => $item) {
            $item->is_rule = 0;
            if ($item->item_exactly) {
                $item->is_rule = 1;
            } else if ($item->item_range_from && $item->item_range_to) {
                $item->is_rule = 2;
            } else if ($item->item_maximum) {
                $item->is_rule = 3;
            }

            $item->meals = ModifierItems::where('modifier_group_id', '=', $item->modifier_group_id)
                ->where('added_from', '=', 2)
                ->where('is_in_stock', '=', 1)
                ->get();
        }

        return response()->json(apiResponseHandler($modifier, 'success', 200));
    }

    public function getEditCartModifiers($id)
    {
        $cartItem = CartItem::where('cart_item_id', '=', $id)->first();
        $modifier = (new ModifierItems())
            ->leftJoin('modifier_groups', 'modifier_groups.modifier_group_id', 'modifier_items.modifier_group_id')
            ->where('item_id', '=', $cartItem['item_id'])
            ->orderBy('modifier_groups.order_no', 'ASC')
            ->get();

        foreach ($modifier as $item) {
            $item->is_rule = 0;

            if ($item->item_exactly) {
                $item->is_rule = 1;
            } else if ($item->item_range_from && $item->item_range_to) {
                $item->is_rule = 2;
            } else if ($item->item_maximum) {
                $item->is_rule = 3;
            }

            $item->meals = (new ModifierItems())
                ->where('modifier_items.modifier_group_id', '=', $item->modifier_group_id)
                ->where('added_from', '=', 2)
                ->select('modifier_items.*', 'cart_details.item_count as count')
                ->leftJoin('cart_details', function ($query) use ($id) {
                    $query
                        ->on('modifier_items.modifier_group_id', '=', 'cart_details.modifier_group_id')
                        ->on('modifier_items.id', '=', 'cart_details.item_id')
                        ->where('cart_details.cart_item_id', '=', $id);
                })
               /* ->leftJoin('items', 'items.item_id', '=', 'modifier_items.item_id')*/
                ->get();
        }
        return response()->json(apiResponseHandler($modifier, 'success', 200));
    }

    public function getMenuTypeMeals($menuTypeId, Request $request)
    {
        if (!!$request->user('api')) {
            $cartList = CartList::where('user_id', '=', $request->user('api')->id)->pluck('cart_list_id');
        } else {
            $cartList = CartList::where('cart_id', '=', $request->get('cart_id'))->pluck('cart_list_id');
        }

        $cartItems = CartItem::whereIn('cart_list_id', $cartList)->pluck('item_id');

        $response = (new CompleteMeals())
            ->with([
                'meal' => function ($query) {
                    $query
                        ->select('items.*')
                        ->groupBy('items.item_id')
                        ->whereNotNull('items.item_id');
                }])
            ->leftJoin('cart_items', function ($join) use ($cartList) {
                $join->on('cart_items.item_id', '=', 'complete_meals.item_id')
                    ->whereIn('cart_list_id', $cartList);
            })
            ->where('complete_meals.menu_id', '=', $menuTypeId)
            ->where('complete_meals.is_active', 1)
            ->whereNotIn('complete_meals.item_id',$cartItems)
            ->orderBy('complete_meals.id', 'DESC')
            ->select(
                'complete_meals.id',
                'cart_items.cart_item_id',
                'cart_items.cart_list_id',
                'cart_items.item_flag',
                'cart_items.menu_id',
                'complete_meals.category_id',
                'complete_meals.item_id',
                'complete_meals.menu_id'
            )->get();

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function singleMenuDetails($itemId, $menuId, Request $request)
    {
        $userId = null;
        if ($request->user('api')) {
            $userId = $request->user('api')->id;
        }
        $response = Item::with(['categoryName' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'item_categories.category_id')
                ->select('item_categories.item_id', 'item_categories.category_id', 'categories.category_name')
                ->get();
        },'favorite' => function ($query) use ($userId) {
            $query->where('user_id', '=', $userId)
                ->select('favorite_items.item_id',
                    DB::raw('(CASE WHEN favorite_items.item_id is NULL THEN 0 ELSE 1 END) as is_favorite')
                )
                ->get();
        }])->where('item_id', '=', $itemId)->get();

        $response[0]['menu_name'] = Menu::where('menu_id', '=', $menuId)->select('menu_name', 'menu_id', 'from', 'to')->first();
        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function singleRestaurantDetails($restaurantId, Request $request)
    {
        $userId = null;
        if ($request->user('api')) {
            $userId = $request->user('api')->id;
        }
        /* if ($request->user('api')) {*/
        $response = Restaurant::with(['favorite' => function ($query) use ($userId) {
            $query->where('user_id', '=', $userId)
                ->select('favorite_restaurants.restaurant_id',
                    DB::raw('(CASE WHEN favorite_restaurants.restaurant_id is NULL THEN 0 ELSE 1 END) as is_favorite')
                )
                ->get();
        }])->where('id', '=', $restaurantId)->first();
        if($response && $response->background_image == ''){
            $response->background_image = 'images/restaurant-images/default_banner.png';
        }
        return response()->json(apiResponseHandler($response, 'success', 200));
        /*} else {
            $response = Restaurant::get();
            return response()->json(apiResponseHandler($response, 'success', 200));
        }*/
    }

    public function getSingleCartDetails($id)
    {
        $cartItem = CartItem::where('cart_item_id', '=', $id)->first();
        $response = Item::with(['categoryName' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'item_categories.category_id')
                ->select('item_categories.item_id', 'item_categories.category_id', 'categories.category_name');
        }])->where('item_id', '=', $cartItem->item_id)->get();

        $response[0]['menu_name'] = Menu::where('menu_id', '=', $cartItem->menu_id)->select('menu_name', 'menu_id', 'from', 'to')->first();

        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function FavoriteRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if (Auth::user()->id) {
            $isAlready = FavoriteRestaurant::where('user_id', '=', Auth::user()->id)
                ->where('restaurant_id', '=', $request->input('restaurant_id'))->get();

            if (count($isAlready)) {
                FavoriteRestaurant::where('user_id', '=', Auth::user()->id)
                    ->where('restaurant_id', '=', $request->input('restaurant_id'))->delete();
                return response()->json(apiResponseHandler([], 'Removed from favorite', 200), 200);
            } else {
                $favoriteOrder = new FavoriteRestaurant();
                $favoriteOrder->restaurant_id = $request->input('restaurant_id');
                $favoriteOrder->user_id = Auth::user()->id;
                $favoriteOrder->save();
                return response()->json(apiResponseHandler([], 'Marked as favorite', 200), 200);
            }
        } else {
            return response()->json(apiResponseHandler([], 'You are not logged in.', 400), 400);
        }
    }

    public function getMostOrderedMenu($type)
    {
        $menu = Menu::where('menu_name',$type)->pluck('menu_id');
        $menuCats = MenuCategory::whereIn('menu_id',$menu)->pluck('category_id');
        $response = (new OrderDetail())
            ->select(DB::raw('COUNT(order_details.item_id) as item_count'), 'items.*')
            ->leftJoin('items', 'items.item_id', '=', 'order_details.item_id')
            ->leftJoin('item_categories', 'item_categories.item_id', '=', 'items.item_id')
            ->whereNotNull('items.item_id')
            ->whereIn('item_categories.category_id',$menuCats)
            ->groupBy('order_details.item_name')
            ->limit('10')
            ->orderBy('item_count', 'DESC')
            ->get();

        return response()->json(apiResponseHandler($response, ''));
    }

    public function validateMenuTiming($menuId){
        $singleMenu = Menu::where('menu_id', '=', $menuId)->first();
        $endTime = strtotime($singleMenu['to']);
        $currentDayName = date('l');
        $message = null;

        if ($currentDayName == 'Saturday' && $endTime < time() && $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_saturday_message'];
            return response()->json(apiResponseHandler([], $message, 400), 400);
        }
        elseif ($currentDayName == 'Sunday' && $singleMenu['menu_name'] == 'Breakfast') {
            $message = $singleMenu['menu_resume_time_message'];
            return response()->json(apiResponseHandler([], $message, 400), 400);
        }else {
            $message = $singleMenu['menu_resume_time_message'];;
        }



        if ($endTime < time()) {
            return response()->json(apiResponseHandler([], $message, 400), 400);
        }
    }

    public function getRecentOrderRestaurant(){
        $lastOrder = Order::where('user_id',Auth::user()->id)
            ->orderBy('order_id','DESC')->first();

        $restaurant = Restaurant::find($lastOrder->restaurant_id);

        return response()->json(apiResponseHandler($restaurant, '', 200), 200);
    }

    public function markItemFavorite(Request $request){
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if (Auth::user()->id) {
            $isAlready = FavoriteItem::where('user_id', '=', Auth::user()->id)
                ->where('item_id', '=', $request->input('item_id'))->get();

            if (count($isAlready)) {
                FavoriteItem::where('user_id', '=', Auth::user()->id)
                    ->where('item_id', '=', $request->input('item_id'))->delete();
                return response()->json(apiResponseHandler([], 'Removed from favorite', 200), 200);
            } else {
                $favoriteOrder = new FavoriteItem();
                $favoriteOrder->item_id = $request->input('item_id');
                $favoriteOrder->user_id = Auth::user()->id;
                $favoriteOrder->save();
                return response()->json(apiResponseHandler([], 'Marked as favorite', 200), 200);
            }
        } else {
            return response()->json(apiResponseHandler([], 'You are not logged in.', 400), 400);
        }
    }
}


