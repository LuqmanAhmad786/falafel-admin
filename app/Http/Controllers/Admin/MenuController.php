<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReward;
use App\Models\AssignAdminReward;
use App\Models\Bonus;
use App\Models\BonusAppliedFor;
use App\Models\Category;
use App\Models\CompleteMeals;
use App\Models\DevicePreference;
use App\Models\Favorite\FavoriteLabel;
use App\Models\FirebaseToken;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ModifierGroupRelations;
use App\Models\ModifierItems;
use App\Models\Order;
use App\Models\Order\OrderDetail;
use App\Models\Restaurant;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use App\Models\SubscriptionPreference;
use App\Models\UserRewards;
use App\OrderRefunds;
use App\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 180);

class MenuController extends Controller
{
    /*public function menuBreakfast()
    {
        $data = Category::all();
        $menuTypes = Menu::all();
        $menus = Meal::with(['subMenu' => function ($join) {
            $join->leftJoin('meal_option_groups', 'meal_option_groups.id', 'main_menu_categories.category_id')
                ->select('main_menu_id', 'meal_option_groups.name');
        }])->where('meal_category_id', '=', '1')->get();
        return view('dashboard.menu-breakfast', ['category' => $data, 'menu' => $menus, 'menu_type' => $menuTypes]);
    }*/

    /*public function lunchBreakfast()
    {
        $data = Category::all();
        $menus = Meal::with(['subMenu' => function ($join) {
            $join->leftJoin('meal_option_groups', 'meal_option_groups.id', 'main_menu_categories.category_id')
                ->select('main_menu_id', 'meal_option_groups.name');
        }])->where('meal_category_id', '=', '2')->get();
        return view('dashboard.menu-lunch', ['category' => $data, 'menu' => $menus]);
    }*/

    public function sideMenuList()
    {
        $data = Category::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $allGroups = ModifierGroup::with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $meal = Item::with(['category' => function ($query) {
            $query->select('categories.category_name', 'item_categories.item_id')
                ->leftJoin('categories', 'categories.category_id', '=', 'item_categories.category_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $menuTypes = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $restaurant = Restaurant::where('id', '=', Session::get('my_restaurant'))->first();

        $anotherLocation = Restaurant::where('id', '!=', Session::get('my_restaurant'))->get();

        return view('dashboard.side-menu-list-view', ['category' => $data, 'menu' => $meal, 'all_groups' => $allGroups, 'menu_type' => $menuTypes, 'restaurant' => $restaurant, 'another_restaurant' => $anotherLocation]);
    }

    public function currentlyUnavailable()
    {
        $data = Category::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $allGroups = ModifierGroup::with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $meal = Item::with(['category' => function ($query) {
            $query->select('categories.category_name', 'item_categories.item_id')
                ->leftJoin('categories', 'categories.category_id', '=', 'item_categories.category_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $meal = Item::with(['category' => function ($query) {
            $query->select('categories.category_name', 'item_categories.item_id')
                ->leftJoin('categories', 'categories.category_id', '=', 'item_categories.category_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $menuTypes = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $restaurant = Restaurant::where('id', '=', Session::get('my_restaurant'))->first();

        $anotherLocation = Restaurant::where('id', '!=', Session::get('my_restaurant'))->get();

        $categories = [];
        $categories = [''];

        $searchSideMenu = Item::query();
        $searchSideMenu->where('restaurant_id', '=', Session::get('my_restaurant'));


        $items = $searchSideMenu->with(['category' => function ($query) {
            $query
                ->leftJoin('categories', 'categories.category_id', 'item_categories.category_id')
                ->select('categories.category_id', 'categories.category_name', 'item_categories.item_id');
        }])
            ->orderBy('order_no', 'ASC')
            ->get();

        $availability = 0;
        foreach ($items as $it) {
            if ($it->is_in_stock == 1) {
                $availability = 1;
            }
        }

        return view('dashboard.unavailable-list-view', ['availability' => $availability, 'items' => $items, 'category' => $data, 'menu' => $meal, 'all_groups' => $allGroups, 'menu_type' => $menuTypes, 'restaurant' => $restaurant, 'another_restaurant' => $anotherLocation]);
    }

    public function searchItemsAvailability(Request $request){
        $menuTypeId = $request->input('menu_type');
        $categoryId = $request->input('category_id');
        $categories = [];

        if (!$categoryId) {
            $categories = MenuCategory::where('menu_id', '=', $menuTypeId)->pluck('category_id');
        } else {
            $categories = [$categoryId];
        }

        $searchSideMenu = Item::query();
        $searchSideMenu->where('restaurant_id', '=', Session::get('my_restaurant'));

        if ($request->input('keyword')) {
            $keyword = $request->input('keyword');
            $searchSideMenu->where(function ($query) use ($keyword) {
                $query->where('items.item_name', 'like', '%' . $keyword . '%');
            });
        }

        $items = $searchSideMenu->with(['category' => function ($query) {
            $query
                ->leftJoin('categories', 'categories.category_id', 'item_categories.category_id')
                ->select('categories.category_id', 'categories.category_name', 'item_categories.item_id');
        }])
            ->when($request->filled('menu_type'), function ($query) use ($categories) {
                $query->leftJoin('item_categories', 'item_categories.item_id', 'items.item_id')
                    ->select('items.*', 'item_categories.category_id')
                    ->whereIn('item_categories.category_id', $categories)
                    ->groupBy('item_categories.item_id');
            })
            ->orderBy('order_no', 'ASC')
            ->get();

        $availability = 0;
        foreach ($items as $it) {
            if ($it->is_in_stock == 1) {
                $availability = 1;
            }
        }

        $response = $items;

        $limit = getPaginationLimit();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);
    }

    public function sideMenuGrid()
    {
        $data = Category::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $allGroups = ModifierGroup::with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $meal = Item::with(['category' => function ($query) {
            $query->select('categories.category_name', 'item_categories.item_id')
                ->leftJoin('categories', 'categories.category_id', '=', 'item_categories.category_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $meal = Item::with(['category' => function ($query) {
            $query->select('categories.category_name', 'item_categories.item_id')
                ->leftJoin('categories', 'categories.category_id', '=', 'item_categories.category_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $menuTypes = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        $restaurant = Restaurant::where('id', '=', Session::get('my_restaurant'))->first();

        $anotherLocation = Restaurant::where('id', '!=', Session::get('my_restaurant'))->get();

        return view('dashboard.side-menu', ['category' => $data, 'menu' => $meal, 'all_groups' => $allGroups, 'menu_type' => $menuTypes, 'restaurant' => $restaurant, 'another_restaurant' => $anotherLocation]);
    }

    public function sideMenuCategories()
    {
        $data = Category::get();
//        $menuTypes = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();
        return view('dashboard.side-menu-categories', ['category' => $data]);
    }

    /* public function resizeImage()
     {
         $meals = Meal::where('meals.thumbnail', 'NOT LIKE', '%menu-thumbnails%')->get();

         foreach ($meals as $meal) {
             $filePath = public_path('/storage/') . $meal->image;
             $tempPath = public_path('images/' . time() . '.png');
             $newPath = 'images/menu-thumbnails/' . time() . '.png';

             $image = Image::make($filePath);
             $image->resize(500, 300);
             $image->save($tempPath);

             Storage::disk('public')->put($newPath, file_get_contents($tempPath));

             Meal::where('id', '=', $meal->id)->update(['thumbnail' => $newPath]);

             $image->destroy();
             unlink($tempPath);
         }
     }*/

    public function addNewSideMenu(Request $request)
    {
        $restaurant = Session::get('my_restaurant');
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $inputData = [
            'item_name' => $request->input('name'),
            'item_price' => $request->input('price'),
            'tax_applicable' => $request->input('tax_applicable'),
            'item_description' => $request->input('description'),
            'its_own' => $request->input('its_own'),
            'is_common' => 1 /*$request->input('is_common')*/, //Changed to 1 after discuss with KPS
            'is_in_stock' => $request->input('is_in_stock'),
            'restaurant_id' => Session::get('my_restaurant'),
            'item_image' => $request->input('image'),
            'item_image_single' => $request->input('image1')
        ];

        /*return $inputData;*/

        $inputData['item_image'] = 'images/menu-images/' . time() . '.png';
        $publicPath = public_path('/storage/') . $inputData['item_image'];
        $tempPath = public_path('images/' . time() . '.png');

        $inputData['item_image_single'] = 'images/menu-images/' . time() . '1.png';
        $publicPath1 = public_path('/storage/') . $inputData['item_image_single'];
        $tempPath1 = public_path('images/' . time() . '1.png');

        $inputData['item_thumbnail'] = 'images/menu-thumbnails/' . time() . '.png';
        $image = file_get_contents($request->input('image'));
        // if($request->input('image1')){
        //     $image1 = file_get_contents($request->input('image1'));
        //     Storage::disk('public')->put($inputData['item_image_single'], $image1);
        // }

        Storage::disk('public')->put($inputData['item_image'], $image);
        // $image = Image::make($publicPath);
        // $image->resize(500, 300);
        // $image->save($tempPath);

        //Storage::disk('public')->put($inputData['item_thumbnail'], file_get_contents($tempPath));

        // $image->destroy();
        // unlink($tempPath);

        if ($request->input('menu_id')) {
            $beforeUpdate = Item::where('item_id', '=', $request->input('menu_id'))->first();

            Item::where('item_id', '=', $request->input('menu_id'))->update($inputData);

            $refItem = Item::where('item_id', '=', $request->input('menu_id'))->first();

            // UPDATE REF ITEM
            if ($refItem['reference_item_id'] != 0) {
                unset($inputData['restaurant_id']);
                Item::where('item_id', '=', $refItem['reference_item_id'])->update($inputData);
            } elseif ($refItem['reference_item_id'] == 0) {
                unset($inputData['restaurant_id']);
                Item::where('reference_item_id', '=', $refItem['item_id'])->update($inputData);
            }

            //UPDATE CATEGORIES
            if ($request->input('category') != '') {
                DB::table('item_categories')
                    ->where('item_id', '=', $request->input('menu_id'))
                    ->delete();
                DB::table('item_categories')->insert([
                    "item_id" => $request->input('menu_id'),
                    "category_id" => $request->input('category')
                ]);
            }

            DB::table('modifier_items')
                ->where('item_id', '=', $request->input('menu_id'))
                ->where('added_from', '=', 1)
                ->delete();
            if ($request->input('modifier') && count($request->input('modifier')) > 0) {
                DB::table('modifier_items')
                    ->where('added_from', '=', 1)
                    ->where("item_id", $request->input('menu_id'))->delete();

                ModifierGroupRelations::where("item_id", $request->input('menu_id'))->delete();

                foreach ($request->input('modifier') as $modifier) {
                    DB::table('modifier_items')->insert([
                        "item_id" => $request->input('menu_id'),
                        "modifier_group_id" => $modifier,
                        "order_no" => 0,
                        "added_from" => 1,
                        "is_in_stock" => $request->input('is_in_stock'),
                    ]);

                    $modifierRelation = new ModifierGroupRelations();
                    $modifierRelation->modifier_group_id = $modifier;
                    $modifierRelation->item_id = $request->input('menu_id');
                    $modifierRelation->save();
                }
            }

            $restaurant = Restaurant::find($restaurant);

            if (Auth::user()->type == 2) {
                $emailTemplate = '<p>Hello,</p>';
                $emailTemplate .= '<p>Item has been updated by ' . $restaurant->name . ' Manager</p>';
                $emailTemplate .= '<p><b>Previous Values</b></p>';
                $emailTemplate .= '<p>Item Name: ' . $beforeUpdate->item_name . '</p>';
                $emailTemplate .= '<p>Item Price: ' . $beforeUpdate->item_price . '</p>';
                $emailTemplate .= '<br/><p><b>New Values</b></p>';
                $emailTemplate .= '<p>Item Name: ' . $inputData['item_name'] . '</p>';
                $emailTemplate .= '<p>Item Price: ' . $inputData['item_price'] . '</p>';

                sendEmailNew($emailTemplate, 'kapil@qualwebs.com', 'Item Updated by ' . $restaurant->name . ' Manager');
            }

            ModifierItems::where('item_id', $request->input('menu_id'))->where('added_from', 2)->update(['is_in_stock' => $request->input('is_in_stock')]);

            $modifiers = $request->input('modifiers');

            if(count($modifiers) > 0){
                $i = 1;
                foreach ($modifiers AS $modifier){
                    ModifierGroup::where('modifier_group_id',$modifier['modifier_group_id'])->update([
                        'order_no' => $i
                    ]);
                    $i++;
                }
            }

            return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
        } else {
            $mealOption = Item::create($inputData);
            $refId = $mealOption->item_id;
            if ($request->input('category') != '') {
                DB::table('item_categories')->insert([
                    "item_id" => $mealOption->item_id,
                    "category_id" => $request->input('category')
                ]);
            }
            foreach ($request->input('modifier') as $modifier) {
                DB::table('modifier_items')->insert([
                    "item_id" => $mealOption->item_id,
                    "modifier_group_id" => $modifier,
                    "order_no" => 0,
                    "added_from" => 1,
                ]);
                $modifierRelation = new ModifierGroupRelations();
                $modifierRelation->modifier_group_id = $modifier;
                $modifierRelation->item_id = $mealOption->item_id;
                $modifierRelation->save();
            }

//            $anotherRes = Restaurant::where('id','!=',$inputData['restaurant_id'])->first();
//            $inputData['restaurant_id'] = $anotherRes['id'];
//            $inputData['reference_item_id'] = $refId;
//            $mealOption = Item::create($inputData);
//            foreach ($request->input('category') as $category) {
////                $catName = Category::where('category_id',$category)->first();
////                $newCat = Category::where('category_name',$catName['category_name'])->where('restaurant_id',$anotherRes['id'])->first();
//                DB::table('item_categories')->insert([
//                    "item_id" => $mealOption->item_id,
//                    "category_id" => $category
//                ]);
//            }
//
//            foreach ($request->input('modifier') as $modifier) {
//                $modName = ModifierGroup::where('modifier_group_id',$modifier)->first();
//                $newModifier = ModifierGroup::where('modifier_group_name',$modName['modifier_group_name'])->where('restaurant_id',$anotherRes['id'])->first();
//                DB::table('modifier_items')->insert([
//                    "item_id" => $mealOption->item_id,
//                    "modifier_group_id" => $newModifier['modifier_group_id'],
//                    "order_no" => 0,
//                    "added_from" => 1,
//                ]);
//                $modifierRelation = new ModifierGroupRelations();
//                $modifierRelation->modifier_group_id = $newModifier['modifier_group_id'];
//                $modifierRelation->item_id = $mealOption->item_id;
//                $modifierRelation->save();
//            }$anotherRes = Restaurant::where('id','!=',$inputData['restaurant_id'])->first();
//            $inputData['restaurant_id'] = $anotherRes['id'];
//            $inputData['reference_item_id'] = $refId;
//            $mealOption = Item::create($inputData);
//            foreach ($request->input('category') as $category) {
////                $catName = Category::where('category_id',$category)->first();
////                $newCat = Category::where('category_name',$catName['category_name'])->where('restaurant_id',$anotherRes['id'])->first();
//                DB::table('item_categories')->insert([
//                    "item_id" => $mealOption->item_id,
//                    "category_id" => $category
//                ]);
//            }
//
//            foreach ($request->input('modifier') as $modifier) {
//                $modName = ModifierGroup::where('modifier_group_id',$modifier)->first();
//                $newModifier = ModifierGroup::where('modifier_group_name',$modName['modifier_group_name'])->where('restaurant_id',$anotherRes['id'])->first();
//                DB::table('modifier_items')->insert([
//                    "item_id" => $mealOption->item_id,
//                    "modifier_group_id" => $newModifier['modifier_group_id'],
//                    "order_no" => 0,
//                    "added_from" => 1,
//                ]);
//                $modifierRelation = new ModifierGroupRelations();
//                $modifierRelation->modifier_group_id = $newModifier['modifier_group_id'];
//                $modifierRelation->item_id = $mealOption->item_id;
//                $modifierRelation->save();
//            }

            return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
        }

    }

    public function addNewCategory(Request $request)
    {
        $restaurant = Session::get('my_restaurant');
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('categories', 'category_name')->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', '=', $restaurant);
                })->ignore($request->input('menu_id'), 'category_id')
            ],
            'menu_array' => 'required|array',
        ], [
            'menu_array.required' => 'Please Select At Least One Menu Type.'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('menu_id')) {
            Category::where('category_id', '=', $request->input('menu_id'))->update([
                'category_name' => $request->input('name'),
                'restaurant_id' => Session::get('my_restaurant')
            ]);

            MenuCategory::where('category_id', '=', $request->input('menu_id'))->delete();

            foreach ($request->input('menu_array') as $val) {
                MenuCategory::create([
                    'category_id' => $request->input('menu_id'),
                    'order_no' => $request->input('order_no'),
                    'menu_id' => $val,
                ]);
            }
            return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
        } else {
            $lastId = Category::create([
                'category_name' => $request->input('name'),
                'order_no' => 0,
                'restaurant_id' => Session::get('my_restaurant')
            ]);

            MenuCategory::where('category_id', '=', $lastId->id)->delete();

            foreach ($request->input('menu_array') as $val) {
                MenuCategory::create([
                    'category_id' => $lastId->category_id,
                    'order_no' => 0,
                    'menu_id' => $val,
                ]);
            }
            return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
        }
    }

    public function deleteMenuOrCategory($menuId, $table)
    {
        if ($table === 'menu') {
            DB::table('menus')->where('menu_id', '=', $menuId)->delete();
            $menuCategory = MenuCategory::where('menu_id', '=', $menuId)->pluck('category_id');
            DB::table('item_categories')->whereIn('category_id', $menuCategory)->delete();
            DB::table('categories')->whereIn('category_id', $menuCategory)->delete();
            DB::table('menu_categories')->where('menu_id', '=', $menuId)->delete();
        } else if ($table === 'modifier_groups') {
            DB::table('modifier_groups')->where('modifier_group_id', '=', $menuId)->delete();
            DB::table('modifier_items')->where('modifier_group_id', '=', $menuId)->delete();
        } else if ($table === 'items') {
            $menu = Item::where('item_id', '=', $menuId)->first();
            if ($menu['reference_item_id'] != 0) {
                $refMenuId = $menu['reference_item_id'];
                DB::table('items')->where('item_id', '=', $menuId)->delete();
                DB::table('item_categories')->where('item_id', '=', $menuId)->delete();
                DB::table('items')->where('item_id', '=', $refMenuId)->delete();
                DB::table('item_categories')->where('item_id', '=', $refMenuId)->delete();
            } else {
                DB::table('items')->where('item_id', '=', $menuId)->delete();
                DB::table('item_categories')->where('item_id', '=', $menuId)->delete();
                $menu = Item::where('reference_item_id', '=', $menu['item_id'])->first();
                DB::table('items')->where('item_id', '=', $menu['item_id'])->delete();
                DB::table('item_categories')->where('item_id', '=', $menu['item_id'])->delete();
            }
        } else if ($table === 'categories') {
            DB::table('categories')->where('category_id', '=', $menuId)->delete();
            DB::table('item_categories')->where('category_id', '=', $menuId)->delete();
            DB::table('menu_categories')->where('category_id', '=', $menuId)->delete();
        } else if ($table === 'bonus') {
            DB::table('bonus')->where('bonus_id', '=', $menuId)->delete();
            DB::table('bonus_applied_for')->where('bonus_id', '=', $menuId)->delete();
        } else if ($table === 'admins') {
            DB::table('admins')->where('id', '=', $menuId)->delete();
        } else if ($table === 'restaurants') {
            DB::table('restaurants')->where('id', '=', $menuId)->delete();
            DB::table('cart_lists')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('categories')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('favorite_restaurants')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('items')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('menus')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('modifier_groups')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('orders')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('rewards_items')->where('restaurant_id', '=', $menuId)->delete();
            DB::table('user_preferences')->where('restaurant_id', '=', $menuId)->delete();
        }

        return response()->json(apiResponseHandler([], 'Deleted successfully.', 200), 200);
    }

    public function getSideMenuCategories($menuId, $type)
    {
        $categories = null;
        if ($type == 'side') {
            $categories = Category::leftJoin('item_categories', function ($query) use ($menuId) {
                $query->on('item_categories.category_id', 'categories.category_id')
                    ->where('item_categories.item_id', '=', $menuId);
            })->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('categories.category_id',
                    'categories.category_name',
                    'categories.order_no',
                    DB::raw('(CASE WHEN item_categories.item_id is NULL THEN 0 ELSE 1 END) as checked'))
                ->get();
        } else if ($type == 'main') {
            $categories = Category::leftJoin('main_menu_categories', function ($query) use ($menuId) {
                $query->on('main_menu_categories.category_id', 'categories.category_id')
                    ->where('main_menu_categories.main_menu_id', '=', $menuId);
            })->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('categories.category_id',
                    'categories.category_name',
                    DB::raw('(CASE WHEN item_categories.item_id is NULL THEN 0 ELSE 1 END) as checked'))->get();
        }

        return response()->json(apiResponseHandler($categories, 'success', 200), 200);
    }

    /*public function menuDetails($id)
    {
        $select = ['meals.id', 'meals.name', 'meals.description', 'meals.price', DB::raw('CONCAT("' . url('/') . '", "/public/storage/", meals.image) as image')];

        $details = Meal::where('id', '=', $id)->select($select)->get()->first();

        $categories = Category::leftJoin('meal_selected_option_groups', function ($query) use ($id) {
            $query
                ->on('meal_selected_option_groups.meal_option_group_id', '=', 'categories.category_id')
                ->where('meal_selected_option_groups.meal_id', '=', $id);
        })->select('categories.name', 'categories.category_id AS category_id',
            DB::raw('(CASE WHEN meal_selected_option_groups.meal_id IS NULL THEN 0 ELSE 1 END) AS selected')
        )->get();

        return view('dashboard.single-menu', ['details' => $details, 'categories' => $categories]);
    }*/

    public function assignSideMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }
    }

    public function getMenusType()
    {
        $response = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();
        return response()->json(apiResponseHandler($response, 'success', 200));
    }

    public function menuTypes()
    {
//        Session::put('my_restaurant', 1);
        $allMenus = Menu::with(['categories' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')->get();
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        foreach ($allMenus as $item) {
            $item->from_time = date('G:i', strtotime($item->from));
            $item->to_time = date('G:i', strtotime($item->to));
        }

        $allItem = Item::all();

//        dd($allMenus);

        return view('dashboard.menu-type', ['all_menus' => $allMenus, 'all_item' => $allItem]);
    }

    public function addMenuType(Request $request)
    {
        $restaurant = Session::get('my_restaurant');
        $validator = Validator::make($request->all(), [
            'menu_name' => [
                'required',
                Rule::unique('menus')->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', '=', $restaurant);
                })->ignore($request->input('menu_id'), 'menu_id')
            ],
            'from' => 'required',
            'to' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('menu_id')) {
            Menu::where('menu_id', '=', $request->input('menu_id'))->update([
                'menu_name' => $request->input('menu_name'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'restaurant_id' => Session::get('my_restaurant'),
            ]);
            return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
        } else {

            $restaurant = Restaurant::where('id', '=', Session::get('my_restaurant'))->first();

            $refText = $restaurant['address'] = '8630 Cullen Blvd, Houston, TX 77051, USA' ? 'C' : 'M';
            $refText = ($request->input('menu_name') == 'Breakfast') ? $refText . '1' : $refText . '2';
            $menuType = new Menu();
            $menuType->menu_name = $request->input('menu_name');
            $menuType->from = $request->input('from');
            $menuType->to = $request->input('to');
            $menuType->restaurant_id = Session::get('my_restaurant');
            $menuType->reference_id_text = $refText;
            $menuType->save();

            return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
        }
    }

    public function allMenu()
    {
        $allMenus = Menu::get();
        return view('dashboard.all-menu', ['all_menus' => $allMenus]);
    }

    public function modifierGroup()
    {
        $allModifiers = ModifierGroup::with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        foreach ($allModifiers as $item) {
            $item->is_rule = 0;
            if ($item->item_exactly) {
                $item->is_rule = 1;
            } elseif ($item->item_range_from && $item->item_range_to) {
                $item->is_rule = 2;
            } elseif ($item->item_maximum) {
                $item->is_rule = 3;
            }
        }

        $allItems = Item::where('menu_type', 1)->where('restaurant_id', '=', Session::get('my_restaurant'))->orderBy('item_name', 'ASC')->get();

        return view('dashboard.modifier-group', ['all_modifiers' => $allModifiers, 'all_items' => $allItems]);
    }

    public function addModifierGroup(Request $request)
    {
        $restaurant = Session::get('my_restaurant');
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'identifier' => [
                'required',
                Rule::unique('modifier_groups', 'modifier_group_identifier')->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', '=', $restaurant);
                })->ignore($request->input('menu_id'), 'modifier_group_id')
            ],
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('menu_id')) {
            ModifierGroup::where('modifier_group_id', '=', $request->input('menu_id'))->update([
                'modifier_group_name' => $request->input('name'),
                'modifier_group_identifier' => $request->input('identifier'),
                'item_exactly' => $request->input('item_exactly'),
                'item_range_from' => $request->input('item_range_from'),
                'item_range_to' => $request->input('item_range_to'),
                'item_maximum' => $request->input('item_maximum'),
                'single_item_maximum' => $request->input('single_item_maximum'),
                'order_no' => $request->input('order_no'),
                'restaurant_id' => Session::get('my_restaurant'),
            ]);

            DB::table('modifier_items')
                ->where('modifier_group_id', '=', $request->input('menu_id'))
                ->delete();

//            foreach ($request->input('items') as $item) {
//                if (($item['item_id'] != null) && ($item['item_name'] != null) && ($item['item_id'] != null)) {
//                    $modifierItems = new ModifierItems();
//                    $modifierItems->modifier_group_id = $request->input('menu_id');
//                    $modifierItems->item_id = $item['item_id'];
//                    $modifierItems->item_name = $item['item_name'];
//                    $modifierItems->item_image = $item['item_image'];
//                    $modifierItems->item_description = $item['item_description'];
//                    $modifierItems->item_price = $item['item_price'];
//                    $modifierItems->its_own = $item['its_own'];
//                    $modifierItems->order_no = $item['order_no'];
//                    $modifierItems->added_from = 2;
//                    $modifierItems->save();
//                }
//            }

            foreach ($request->input('items') as $item) {
                if (($item['item_name'] != null)) {
                    $mItemId = Item::create([
                        'item_name' => $item['item_name'],
                        'item_price' => $item['item_price'],
                        'restaurant_id' => Session::get('my_restaurant'),
                        'item_description' => $item['item_name'],
                        'menu_type' => 2
                    ]);
                    ModifierItems::create([
                        'modifier_group_id' => $request->input('menu_id'),
                        'item_id' => $mItemId->item_id,
                        'added_from' => 2,
                        'item_name' => $item['item_name'],
                        'item_price' => $item['item_price']
                    ]);
                }
            }
            if (count($request->input('selected_items')) > 0) {
                foreach ($request->input('selected_items') as $selectItem) {
                    ModifierItems::create([
                        'modifier_group_id' => $request->input('menu_id'),
                        'item_id' => $selectItem,
                        'added_from' => 1
                    ]);
                    ModifierGroupRelations::create([
                        'modifier_group_id' => $request->input('menu_id'),
                        'item_id' => $selectItem
                    ]);
                }
            }
            return response()->json(apiResponseHandler([], 'Modifier Updated Successfully', 200), 200);
        } else {
            $modifier = new ModifierGroup();
            $modifier->modifier_group_name = $request->input('name');
            $modifier->modifier_group_identifier = $request->input('identifier');
            $modifier->item_exactly = $request->input('item_exactly');
            $modifier->item_range_from = $request->input('item_range_from');
            $modifier->item_range_to = $request->input('item_range_to');
            $modifier->item_maximum = $request->input('item_maximum');
            $modifier->single_item_maximum = $request->input('single_item_maximum');
            $modifier->order_no = $request->input('order_no');
            $modifier->restaurant_id = Session::get('my_restaurant');
            $modifier->save();

            foreach ($request->input('items') as $item) {
                if (($item['item_name'] != null) && ($item['item_price'] != null)) {
                    $mItemId = Item::create([
                        'item_name' => $item['item_name'],
                        'item_price' => $item['item_price'],
                        'restaurant_id' => Session::get('my_restaurant'),
                        'item_description' => $item['item_name'],
                        'menu_type' => 2
                    ]);
                    ModifierItems::create([
                        'modifier_group_id' => $modifier->modifier_group_id,
                        'item_id' => $mItemId->item_id,
                        'added_from' => 2,
                        'item_name' => $item['item_name'],
                        'item_price' => $item['item_price']
                    ]);
                }
            }
            if (count($request->input('selected_items')) > 0) {
                foreach ($request->input('selected_items') as $selectItem) {
                    ModifierItems::create([
                        'modifier_group_id' => $modifier->modifier_group_id,
                        'item_id' => $selectItem,
                        'added_from' => 1
                    ]);
                    ModifierGroupRelations::create([
                        'modifier_group_id' => $modifier->modifier_group_id,
                        'item_id' => $selectItem
                    ]);
                }
            }
            return response()->json(apiResponseHandler([], 'Modifier Added Successfully', 200));
        }
    }

    public function updateModGrpItemPrice(Request $request){
        $restaurant = Session::get('my_restaurant');
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'item_price' => 'required',
            'menu_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        Item::where('item_id',$request->input('item_id'))->where('restaurant_id', $restaurant)->update([
            'item_price' => $request->input('item_price')
        ]);
        ModifierItems::where('modifier_group_id',$request->input('menu_id'))->where('item_id',$request->input('item_id'))
            ->update([
                'item_price' => $request->input('item_price')
            ]);
        return response()->json(apiResponseHandler([], 'Modifier item price updated successfully', 200));
    }

    public function deleteModGrpItem(Request $request){
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'menu_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $modifierItem = ModifierItems::where('modifier_group_id',$request->input('menu_id'))->where('item_id',$request->input('item_id'))
            ->first();
        if($modifierItem){
            ModifierItems::where('modifier_group_id',$request->input('menu_id'))->where('item_id',$request->input('item_id'))->delete();
            return response()->json(apiResponseHandler([], 'Modifier item deleted successfully', 200));
        } else{
            return response()->json(apiResponseHandler([], 'Invalid modifier item selected. Please select valid item.', 400));
        }
    }

    public function getModifierItems($modifierId, $isWhat)
    {
        $items = null;
        if ($isWhat == 'modifier') {
            $items = Item::leftJoin('modifier_items', function ($query) use ($modifierId) {
                $query->on('modifier_items.item_id', 'items.item_id')
                    ->where('modifier_items.modifier_group_id', '=', $modifierId)
                    ->where('modifier_items.added_from', '=', 1);
            })->where('restaurant_id', '=', Session::get('my_restaurant'))->select('items.item_id', 'items.item_name',
                DB::raw('(CASE WHEN modifier_items.modifier_group_id is NULL THEN 0 ELSE 1 END) as checked'))->get();
        } else if ($isWhat == 'item') {
            $items = ModifierGroup::with(['items' => function ($query) {
                $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
            }])->leftJoin('modifier_items', function ($query) use ($modifierId) {
                $query->on('modifier_items.modifier_group_id', 'modifier_groups.modifier_group_id')
                    ->where('modifier_items.item_id', '=', $modifierId)
                    ->where('modifier_items.added_from', '=', 1);
            })->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('modifier_groups.modifier_group_id', 'modifier_groups.modifier_group_name', 'modifier_groups.modifier_group_identifier', 'modifier_items.order_no',
                    DB::raw('(CASE WHEN modifier_items.item_id is NULL THEN 0 ELSE 1 END) as checked'))
                ->orderBy('modifier_groups.order_no','ASC')
                ->get();
        }

        return response()->json(apiResponseHandler($items, 'success', 200), 200);
    }

    public function getCategoriesMenu($categoryId)
    {
        $response = Menu::leftJoin('menu_categories', function ($query) use ($categoryId) {
            $query->on('menu_categories.menu_id', 'menus.menu_id')
                ->where('menu_categories.category_id', '=', $categoryId);
        })->select('menus.menu_id', 'menus.menu_name',
            DB::raw('(CASE WHEN menu_categories.category_id is NULL THEN 0 ELSE 1 END) as checked'))
            ->where('restaurant_id', '=', Session::get('my_restaurant'))->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function modifierSideMenu($modifierId)
    {
        $response = ModifierGroup::with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id');
        }])->where('modifier_groups.modifier_group_id', '=', $modifierId)->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function addMenuTypeMeals(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required',
            'selected_meals' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        MenuItem::where('menu_id', '=', $request->input('menu_id'))->delete();

        foreach ($request->input('selected_meals') as $item) {
            $selectedMeals = new MenuItem();
            $selectedMeals->menu_id = $request->input('menu_id');
            $selectedMeals->item_id = $item;
            $selectedMeals->save();
        }

        return response()->json(apiResponseHandler([], 'success', 200), 200);
    }

    public function getSelectedMeal($menuId)
    {
        /* $response = Menu::leftJoin('menu_items', function ($query) use ($menuId) {
             $query->where('menu_items.item_id', '=', $menuId);
         })->where('restaurant_id', '=', Session::get('my_restaurant'))
             ->groupBy('menus.menu_id')
             ->select('menus.menu_name', 'menu_items.menu_id',
                 DB::raw('(CASE WHEN menu_items.menu_id is NULL THEN 0 ELSE 1 END) as checked'))
             ->get();*/

        $response = Menu::leftJoin('menu_items', 'menu_items.menu_id', 'menus.menu_id')
            ->where('restaurant_id', '=', Session::get('my_restaurant'))
            ->select('menus.menu_name', 'menus.menu_id',
                DB::raw('(CASE WHEN menu_items.menu_id is NULL THEN 0 ELSE 1 END) as checked'))
            ->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function loadCompleteMeal()
    {
        $response = Menu::with(['completeMeal' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'complete_meals.category_id')
                ->leftJoin('items', 'items.item_id', 'complete_meals.item_id')
                ->select('complete_meals.menu_id', 'complete_meals.category_id', 'categories.category_name', 'items.item_name')
                ->groupBy('complete_meals.category_id', 'complete_meals.menu_id', 'complete_meals.menu_id')
                ->get();
        }])->where('menus.restaurant_id', '=', Session::get('my_restaurant'))->get();
        return view('dashboard.complete-meals', ['response' => $response]);
    }

    public function onRestaurantChange($restaurantId)
    {
        Session::put('my_restaurant', $restaurantId);
        return response()->json(apiResponseHandler([], 'Success', 200), 200);
    }

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $message = null;
        $restaurant_image = '';
        if($request->filled('background_image')){
            $restaurant_image = 'images/restaurant-images/' . time() . '.png';
            $image = file_get_contents($request->input('background_image'));
            Storage::disk('public')->put($restaurant_image, $image);
        }

        if ($request->input('id')) {
            Restaurant::where('id', '=', $request->input('id'))->update([
                'name' => $request->input('name'),
                'slug' => strtolower($request->input('slug')),
                'category_emoji' => $request->input('category_emoji'),
                'address' => $request->input('address'),
                'contact_number' => $request->input('contact_number'),
                'additional_info' => $request->input('additional_info'),
                'tax_rate' => $request->input('tax_rate'),
                'timezone' => $request->input('timezone'),
                'time_interval' => $request->input('time_interval'),
                'clover_mid' => $request->input('clover_mid'),
                'clover_api_key' => $request->input('clover_api_key'),
                'clover_order_type_id' => $request->input('clover_order_type_id'),
                'clover_payment_api_key' => $request->input('clover_payment_api_key'),
                'clover_payment_api_token' => $request->input('clover_payment_api_token'),
                'clover_employee_id' => $request->input('clover_employee_id'),
                'clover_tender_id' => $request->input('clover_tender_id'),
                'emails' => $request->input('emails'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'about' => $request->input('about'),
                'background_image' => $restaurant_image,
                'status' => $request->input('status'),
                'is_comingsoon' => $request->input('is_comingsoon'),
                'commission_type' => $request->input('commission_type'),
                'commission' => $request->filled('commission') ? $request->input('commission') : 0,
                'preparation_time' => $request->input('preparation_time')
            ]);
            $message = 'Updated Successfully';
        } else {
            $restaurant = Restaurant::create([
                'name' => $request->input('name'),
                'slug' => strtolower($request->input('slug')),
                'category_emoji' => $request->input('category_emoji'),
                'address' => $request->input('address'),
                'contact_number' => $request->input('contact_number'),
                'additional_info' => $request->input('additional_info'),
                'tax_rate' => $request->input('tax_rate'),
                'timezone' => $request->input('timezone'),
                'time_interval' => $request->input('time_interval') ? $request->input('time_interval') : 15,
                'clover_mid' => $request->input('clover_mid'),
                'clover_api_key' => $request->input('clover_api_key'),
                'clover_order_type_id' => $request->input('clover_order_type_id'),
                'clover_payment_api_key' => $request->input('clover_payment_api_key'),
                'clover_payment_api_token' => $request->input('clover_payment_api_token'),
                'clover_employee_id' => $request->input('clover_employee_id'),
                'clover_tender_id' => $request->input('clover_tender_id'),
                'emails' => $request->input('emails'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'about' => $request->input('about'),
                'background_image' => $restaurant_image,
                'status' => $request->input('status'),
                'is_comingsoon' => $request->input('is_comingsoon'),
                'commission_type' => $request->input('commission_type'),
                'commission' => $request->filled('commission') ? $request->input('commission') : 0,
                'preparation_time' => $request->input('preparation_time')
            ]);
            $message = 'Added Successfully';

            //Menu for new added location

            $menu = Menu::create(['restaurant_id' => $restaurant->id,
                'menu_name' => 'Main',
                'from' => '11:00:00',
                'to' => '21:00:00',
                'reference_id_text' => 'C1'
            ]);

            // GENERATE DEFAULT TIMINGS
            $this->generateMenuTimings($restaurant->id, $menu->menu_id);
        }

        return response()->json(apiResponseHandler([], $message, 200), 200);
    }

    public function generateMenuTimings($res,$menuId){
        Restaurant\RestaurantMenuTiming::insert([
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'monday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'tuesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'wednesday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'thursday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'friday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'saturday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ],
            [
                'restaurant_menu_id' => $menuId,
                'day' => 'sunday',
                'from_1' => '07:00:00',
                'to_1' => '10:00:00',
                'from_2' => '11:00:00',
                'to_2' => '8:00:00',
            ]
        ]);

        $data = [
            [
                'item_id' => 679,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 25,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 680,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 30,
                'is_for_gold_only' => 1,
            ],
            [
                'item_id' => 681,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 50,
                'is_for_gold_only' => 1,
            ],
            [
                'item_id' => 682,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 50,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 683,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 100,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 684,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 200,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 685,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 300,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 686,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 400,
                'is_for_gold_only' => 0,
            ],
            [
                'item_id' => 687,
                'restaurant_id' => $res,
                'is_enable' => 1,
                'flag' => 1,
                'points_required' => 500,
                'is_for_gold_only' => 0,
            ],
        ];
        RewardsItem::insert($data);
    }

    public function singleSideMenu($menuId)
    {
        $response = Item::where('items.item_id', '=', $menuId)->first();
        return response()->json(apiResponseHandler($response, 'Updated Successfully', 200), 200);
    }

    public function singleCategory($categoryId)
    {
        $response = Category::leftJoin('menu_categories', 'menu_categories.category_id', 'categories.category_id')
            ->where('categories.category_id', '=', $categoryId)
            ->select(
                'categories.category_id',
                'categories.restaurant_id',
                'categories.category_name',
                'menu_categories.order_no',
                'categories.clover_id'
            )
            ->first();
        return response()->json(apiResponseHandler($response, 'Successfully', 200), 200);
    }

    public function searchSideMenu(Request $request)
    {
        $menuTypeId = $request->input('menu_type');
        $categoryId = $request->input('category_id');
        $categories = [];

        if (!$categoryId) {
            $categories = MenuCategory::where('menu_id', '=', $menuTypeId)->pluck('category_id');
        } else {
            $categories = [$categoryId];
        }


        $searchSideMenu = Item::query();
        $searchSideMenu->where('restaurant_id', '=', Session::get('my_restaurant'));
        $searchSideMenu->where('menu_type', '=', 1);

        if ($request->input('keyword')) {
            $keyword = $request->input('keyword');
            $searchSideMenu->where(function ($query) use ($keyword) {
                $query->where('items.item_name', 'like', '%' . $keyword . '%');
            });
        }

        $response = $searchSideMenu->with(['category' => function ($query) {
            $query
                ->leftJoin('categories', 'categories.category_id', 'item_categories.category_id')
                ->select('categories.category_id', 'categories.category_name', 'item_categories.item_id');
        }])
            ->when($request->filled('menu_type'), function ($query) use ($categories) {
                $query->leftJoin('item_categories', 'item_categories.item_id', 'items.item_id')
                    ->select('items.*', 'item_categories.category_id')
                    ->whereIn('item_categories.category_id', $categories)
                    ->groupBy('item_categories.item_id');
            })
            ->orderBy('order_no', 'ASC')
            ->get();


        $limit = getPaginationLimit();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);
    }

    public function changeItemToUnavailable($itemId)
    {
        $ifFound = Item::where('item_id', '=', $itemId)->get();
        if (count($ifFound) > 0) {
            ModifierItems::where('item_id', $itemId)->where('added_from', 2)->update(['is_in_stock' => 0]);
            Item::where('item_id', $itemId)->update(['is_in_stock' => 0]);
            return response()->json(apiResponseHandler([], 'Updated Successfully'));
            /*$template = view('email-templates.item-updated', [
                'name' => $ifFound[0]->item_name,
                'price' => $ifFound[0]->item_price,
                'tax_rate' => $ifFound[0]->tax_rate,
                'description' => $ifFound[0]->item_description,
                'is_in_stock' => 'No',
            ])->render();*/

            //sendEmail($template, 'superadmin@tcr.com', 'Item Updated: The Chocolate Room');
        }
        return redirect('menu/items-availability');
    }

    public function changeItemToAvailable($itemId)
    {
        $ifFound = Item::where('item_id', '=', $itemId)->get();
        if (count($ifFound) > 0) {
            ModifierItems::where('item_id', $itemId)->where('added_from', 2)->update(['is_in_stock' => 1]);
            Item::where('item_id', $itemId)->update(['is_in_stock' => 1]);
            return response()->json(apiResponseHandler([], 'Updated Successfully'));
            /*$template = view('email-templates.item-updated', [
                'name' => $ifFound[0]->item_name,
                'price' => $ifFound[0]->item_price,
                'tax_rate' => $ifFound[0]->tax_rate,
                'description' => $ifFound[0]->item_description,
                'is_in_stock' => 'Yes',
            ])->render();*/

            //sendEmail($template, 'superadmin@tcr.com', 'Item Updated: The Chocolate Room');
        }
        return redirect('menu/items-availability');
    }

    public function allItemToUnavailable()
    {
        ModifierItems::where('added_from', 2)->update(['is_in_stock' => 0]);
        Item::where('is_in_stock', 1)->update(['is_in_stock' => 0]);
        return response()->json(apiResponseHandler([], 'Updated Successfully'));
    }

    public function allItemToAvailable()
    {
        ModifierItems::where('added_from', 2)->update(['is_in_stock' => 1]);
        Item::where('is_in_stock', 0)->update(['is_in_stock' => 1]);
        return response()->json(apiResponseHandler([], 'Updated Successfully'));
    }

    public function copyFromAnotherLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'another_restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        /*copy categories*/
        $categories = Category::where('restaurant_id', '=', $request->input('another_restaurant_id'))->get();
        foreach ($categories as $val) {
            Category::create([
                'restaurant_id' => $request->input('restaurant_id'),
                'category_name' => $val['category_name']
            ]);
        }

        /*copy menus*/
        $menus = Menu::where('restaurant_id', '=', $request->input('another_restaurant_id'))->get();
        if (count($menus)) {
            foreach ($menus as $val) {
                $entry = new Menu();
                $entry->restaurant_id = $request->input('restaurant_id');
                $entry->menu_name = $val['menu_name'];
                $entry->from = $val['from'];
                $entry->to = $val['to'];
                $entry->save();

                $menuCategory = MenuCategory::where('menu_id', '=', $val['menu_id'])->get();
                foreach ($menuCategory as $v) {
                    $category = Category::find($v['category_id']);
                    $newCategory = Category::where('category_name', '=', $category['category_name'])
                        ->where('restaurant_id', '=', $request->input('restaurant_id'))
                        ->first();
                    if ($newCategory['category_id']) {
                        MenuCategory::create([
                            'menu_id' => $entry['menu_id'],
                            'category_id' => $newCategory['category_id']
                        ]);
                    }
                }
            }
        }

        /*copy items*/
        $items = Item::where('restaurant_id', '=', $request->input('another_restaurant_id'))->get();
        foreach ($items as $val) {
            $entry = new Item();
            $entry->restaurant_id = $request->input('restaurant_id');
            $entry->item_name = $val['item_name'];
            $entry->item_price = $val['item_price'];
            $entry->tax_rate = $val['tax_rate'];
            $entry->item_image = $val['item_image'];
            $entry->item_thumbnail = $val['item_thumbnail'];
            $entry->item_description = $val['item_description'];
            $entry->its_own = $val['its_own'];
            $entry->order_no = $val['order_no'];
            $entry->complete_meal_of = $val['complete_meal_of'];
            $entry->reference_item_id = $val['item_id'];
            $entry->save();

            $itemCategories = ItemCategory::where('item_id', '=', $val['item_id'])->get();
            foreach ($itemCategories as $v) {

                $category = Category::find($v['category_id']);
                $newCategory = Category::where('category_name', '=', $category['category_name'])
                    ->where('restaurant_id', '=', $request->input('restaurant_id'))
                    ->first();

                if ($newCategory['category_id']) {
                    ItemCategory::create([
                        'item_id' => $entry['item_id'],
                        'category_id' => $newCategory['category_id']
                    ]);
                }
            }
        }

        /*copy modifier groups*/
        $modifierGroup = ModifierGroup::where('restaurant_id', '=', $request->input('another_restaurant_id'))->get();
        foreach ($modifierGroup as $val) {
            $entry = new ModifierGroup();
            $entry->restaurant_id = $request->input('restaurant_id');
            $entry->modifier_group_name = $val['modifier_group_name'];
            $entry->item_exactly = $val['item_exactly'];
            $entry->item_range_from = $val['item_range_from'];
            $entry->item_range_to = $val['item_range_to'];
            $entry->item_maximum = $val['item_maximum'];
            $entry->single_item_maximum = $val['single_item_maximum'];
            $entry->save();

            $modifierItems = ModifierItems::where('modifier_group_id', '=', $val['modifier_group_id'])->get();
            foreach ($modifierItems as $v) {

                $item = Item::find($v['item_id']);
                $newItem = Item::where('item_name', '=', $item['item_name'])
                    ->where('item_price', '=', $item['item_price'])
                    ->where('restaurant_id', '=', $request->input('restaurant_id'))
                    ->first();

                if ($newItem['item_id']) {
                    $sub_entry = new ModifierItems();
                    $sub_entry->modifier_group_id = $entry['modifier_group_id'];
                    $sub_entry->item_id = $newItem['item_id'];
                    $sub_entry->added_from = $v['added_from'];
                    $sub_entry->save();

                    $modifierRelation = new ModifierGroupRelations();
                    $modifierRelation->modifier_group_id = $entry['modifier_group_id'];
                    $modifierRelation->item_id = $newItem['item_id'];
                    $modifierRelation->save();
                }
            }
        }

        return response()->json(apiResponseHandler([], 'Copied Successfully'));
    }

    public function loadOtherLocation($restaurant_id)
    {
        $response = Menu::with(['categories' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')
                ->select('menu_categories.menu_id', 'menu_categories.category_id', 'categories.category_name')
                ->get();
        }])->where('restaurant_id', '=', $restaurant_id)->get();

        foreach ($response as $item) {
            foreach ($item['categories'] as $v) {
                $v['items'] = ItemCategory::where('category_id', '=', $v['category_id'])
                    ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                    ->get();
            }
        }
        return view('dashboard.other-location-menu', ['menus' => $response]);
    }

    public function singleItemModifiers($itemId)
    {
        $modifier = ModifierItems::leftJoin('modifier_groups', 'modifier_groups.modifier_group_id', 'modifier_items.modifier_group_id')
            ->where('item_id', '=', $itemId)
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

            $item->meals = ModifierItems::where('modifier_group_id', '=', $item->modifier_group_id)
                ->leftJoin('items', 'items.item_id', 'modifier_items.item_id')
                ->select('items.*')
                ->where('added_from', '=', 2)
                ->get();
        }
        return response()->json(apiResponseHandler($modifier, 'success', 200));
    }

    public function getRestaurantMenu($restaurantId)
    {
        $response = Item::where('restaurant_id', '=', $restaurantId)->get();
        return response()->json(apiResponseHandler($response, 'Success'));
    }

    public function loadRewardsItem()
    {
        $category = Category::where('restaurant_id', '=', Session::get('my_restaurant'))->get();
        $rewardsItem = RewardsItem::with('item')
            ->leftJoin('categories', 'categories.category_id', 'rewards_items.category_id')
            ->where('rewards_items.restaurant_id', '=', Session::get('my_restaurant'))
            ->where('flag', '=', 1)
            ->select('rewards_items.*', 'categories.category_name')
            ->get();

        $birthdayItem = RewardsItem::with('item')
            ->leftJoin('categories', 'categories.category_id', 'rewards_items.category_id')
            ->where('rewards_items.restaurant_id', '=', Session::get('my_restaurant'))
            ->where('flag', '=', 2)
            ->select('rewards_items.*', 'categories.category_name')
            ->get();

        $adminReward['users'] = User::get();
        /*return [$rewardsItem, $birthdayItem];*/

        $adminRewardList = AdminReward::with(['users' => function ($query) {
            $query->leftJoin('users', 'users.id', 'assign_admin_rewards.user_id')
                ->select('assign_admin_rewards.admin_reward_id',
                    'users.first_name', 'users.last_name', 'users.id as user_id',
                    DB::raw("CONCAT(users.first_name, ' ' , users.last_name) as full_name"))
                ->get();
        }])->groupBy('unique_key')
            ->get();

        foreach ($adminRewardList as $value) {
            if ($value['item_id']) {
                $value['all_items'] = AdminReward::where('unique_key', '=', $value['unique_key'])
                    ->leftJoin('items', 'items.item_id', 'admin_rewards.item_id')
                    ->pluck('items.item_name');

                $value['all_items'] = $value['all_items']->toArray();
            }
            if ($value['reward_point']) {
                $value['reward_point'] = AdminReward::where('unique_key', '=', $value['unique_key'])->select('reward_point')->first();
            }
        }

        return view('dashboard.rewards-item',
            [
                'category' => $category,
                'reward_item' => $rewardsItem,
                'birthday_item' => $birthdayItem,
                'admin_reward' => $adminReward,
                'admin_reward_list' => $adminRewardList,
            ]
        );
    }

    public function addRewardsItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'flag' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $categoryItem = ItemCategory::where('category_id', '=', $request->input('category_id'))->get();

        if (count($categoryItem)) {
            foreach ($categoryItem as $key => $item) {
                $isItemExist = Item::where('item_id', '=', $item['item_id'])->get();
                if (count($isItemExist)) {
                    $isAlready = RewardsItem::where('item_id', '=', $item['item_id'])
                        ->where('flag', '=', $request->input('flag'))
                        ->get();
                    if (count($isAlready)) {
                    } else {
                        $rewardItem = new RewardsItem();
                        $rewardItem->item_id = $item['item_id'];
                        $rewardItem->category_id = $request->input('category_id');
                        $rewardItem->restaurant_id = Session::get('my_restaurant');
                        $rewardItem->is_enable = 1;
                        $rewardItem->flag = $request->input('flag');
                        $rewardItem->save();
                    }
                } else {
                }
            }
            return response()->json(apiResponseHandler([], 'Item Added Successfully.'));
        } else {
            return response()->json(apiResponseHandler([], 'No item added in selected category.', 400), 400);
        }
    }

    public function rewardItemStatus($rewardItemId, $status)
    {
        if ($rewardItemId) {
            RewardsItem::where('reward_item_id', '=', $rewardItemId)->delete();
            return response()->json(apiResponseHandler([], 'Item Deleted Successfully.'));
        } else {
            return response()->json(apiResponseHandler([], 'Invalid reward id.', 400), 400);
        }
    }

    public function getMenuCategory($menuId)
    {
        $response = MenuCategory::with(['category'])
            ->where('menu_id', '=', $menuId)
            /*->orderBy('menu_name', 'ASC')*/
            ->get();
        return response()->json(apiResponseHandler($response));
    }

    /**
     *
     */

    public function loadDashboard()
    {
        $details = null;

        if(Session::get('my_restaurant') != 'all') {
            $details['total_users'] = Order::where('user_id','!=',0)
                ->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->count(DB::raw('DISTINCT user_id'));
        }else{
            $details['total_users'] = User::count();
        }

        if(Session::get('my_restaurant') != 'all'){
            $details['total_items'] = count(Item::where('restaurant_id', '=', Session::get('my_restaurant'))->get());
        }else{
            $details['total_items'] = Item::count();
        }

        if(Session::get('my_restaurant') != 'all'){
            $details['total_orders'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))->get());
        }
        else{
            $details['total_orders'] = Order::count();
        }

        if(Session::get('my_restaurant') != 'all') {
            $details['recent_orders'] = Order\Order::with(['orderDetails' => function ($query) {
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
                    )->whereNotNull('items.item_id')
                    ->get();
            }])->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->leftJoin('users', 'users.id', 'orders.user_id')
                ->orderBy('orders.created_at', 'DESC')
                ->select('orders.order_id',
                    'orders.reference_id',
                    'orders.total_amount',
                    'orders.created_at',
                    'orders.user_first_name',
                    'orders.user_last_name',
                    'users.first_name',
                    'users.last_name',
                    DB::raw('DATE_FORMAT(orders.created_at, \'%M %D %Y\') as order_date'),
                    DB::raw('DATE_FORMAT(orders.created_at, \'%r\') as order_time'),
                    'orders.created_at',
                    DB::raw('DATE_FORMAT(orders.created_at, \'%Y/%m/%d\') as order_date_f'))
                ->limit(5)
                ->get();

            foreach ($details['recent_orders'] as $value) {
                if ($value['order_date_f'] == date("Y/m/d")) {
                    $value['order_date_f'] = 'Today';
                } else {
                    $value['order_date_f'] = $value['order_date'];
                }
            }
        }else{
            $details['recent_orders'] = Order\Order::with(['orderDetails' => function ($query) {
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
                    )->whereNotNull('items.item_id')
                    ->get();
            }])->leftJoin('users', 'users.id', 'orders.user_id')
                ->orderBy('orders.created_at', 'DESC')
                ->select('orders.order_id',
                    'orders.reference_id',
                    'orders.total_amount',
                    'orders.created_at',
                    'orders.user_first_name',
                    'orders.user_last_name',
                    'users.first_name',
                    'users.last_name',
                    DB::raw('DATE_FORMAT(orders.created_at, \'%M %D %Y\') as order_date'),
                    DB::raw('DATE_FORMAT(orders.created_at, \'%r\') as order_time'),
                    'orders.created_at',
                    DB::raw('DATE_FORMAT(orders.created_at, \'%Y/%m/%d\') as order_date_f'))
                ->limit(5)
                ->get();

            foreach ($details['recent_orders'] as $value) {
                if ($value['order_date_f'] == date("Y/m/d")) {
                    $value['order_date_f'] = 'Today';
                } else {
                    $value['order_date_f'] = $value['order_date'];
                }
            }
        }

        if(Session::get('my_restaurant') != 'all'){
            $details['top_selling_item'] = OrderDetail::with(['item'])
                ->leftJoin('orders', 'orders.order_id', '=', 'order_details.order_id')
                ->where('orders.restaurant_id', '=', Session::get('my_restaurant'))
                ->select(
                    'order_details.item_id', DB::raw('COUNT(item_id) as top_count'))
                ->groupBy('item_id')
                ->orderBy('top_count', 'DESC')
                ->take(12)
                ->get();

        }
        else{
            $details['top_selling_item'] = OrderDetail::with(['item'])
                ->leftJoin('orders', 'orders.order_id', '=', 'order_details.order_id')
                ->select(
                    'order_details.item_id', DB::raw('COUNT(item_id) as top_count'))
                ->groupBy('item_id')
                ->orderBy('top_count', 'DESC')
                ->take(10)
                ->get();            
                
        }
        

        $details['top_zipcodes'] = Order::leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.restaurant_id', '=', Session::get('my_restaurant'))
            ->select(
                'users.zip_code', DB::raw('COUNT(orders.order_id) as top_count'))
            ->groupBy('users.zip_code')
            ->orderBy('top_count', 'DESC')
            ->get();


        if(Session::get('my_restaurant') != 'all'){
            $details['today_sales'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                ->select('created_at', 'total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();

            $details['yesterday_sales'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(subdate(current_date, 1),'%Y%c%d')"))
                ->select('created_at', 'total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();

            $details['gross_revenue'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();
            $details['discount_amount'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('discount_amount', DB::raw('SUM(discount_amount) as discount_amount'))
                ->first();

            $details['total_tax'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->select('total_tax', DB::raw('SUM(total_tax) as total_tax'))
                ->first();
            $details['refund_amount'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('payment_status', 2)
                ->select('total_amount', DB::raw('SUM(total_amount) as refund_amount'))
                ->first();
            $details['categories'] = Category::with(['sideMenu'])
                ->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->get();

            $details['top_orders'] = Order::join('users', 'orders.user_id', 'users.id')->where('restaurant_id', '=', Session::get('my_restaurant'))->select('orders.user_id', DB::raw('concat(users.first_name, " " ,users.last_name) as name'), DB::raw('COUNT(DISTINCT orders.order_id) as order_count'), DB::raw('SUM(orders.total_amount) as total_amount'), DB::raw('MAX(orders.created_at) as last_order_at'))->groupBy('orders.user_id')->orderBy('total_amount', 'DESC')->limit(8)->get();
        }
        else{
            $details['today_sales'] = Order::where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                ->select('created_at', 'total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();

            $details['yesterday_sales'] = Order::where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(subdate(current_date, 1),'%Y%c%d')"))
                ->select('created_at', 'total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();

            $details['gross_revenue'] = Order::select('total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();
            $details['discount_amount'] = Order::select('discount_amount', DB::raw('SUM(discount_amount) as discount_amount'))
                ->first();

            $details['total_tax'] = Order::select('total_tax', DB::raw('SUM(total_tax) as total_tax'))
                ->first();
            $details['refund_amount'] = Order::where('payment_status', 2)
                ->select('total_amount', DB::raw('SUM(total_amount) as refund_amount'))
                ->first();
            $details['categories'] = Category::with(['sideMenu'])->get();

            $details['top_orders'] = Order::join('users', 'orders.user_id', 'users.id')->select('orders.user_id', DB::raw('concat(users.first_name, " " ,users.last_name) as name'), DB::raw('COUNT(DISTINCT orders.order_id) as order_count'), DB::raw('SUM(orders.total_amount) as total_amount'), DB::raw('MAX(orders.created_at) as last_order_at'))->groupBy('orders.user_id')->orderBy('total_amount', 'DESC')->limit(8)->get();
        }

        return view('dashboard.dashboard', ['details' => $details]);
    }


    public function onStatsChange(Request $request)
    {
        $split_date = explode('-', $request->input('full_date'));

        if ($request->filled('full_date')) {
            $start_date = date('Ymd', strtotime($split_date[0]));
            $end_date = date('Ymd', strtotime($split_date[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        if(Session::get('my_restaurant') != 'all'){

            $details['gross_revenue'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();


            $details['discount_amount'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('discount_amount', DB::raw('SUM(discount_amount) as discount_amount'))
                ->first();

            $details['total_tax'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('total_tax', DB::raw('SUM(total_tax) as total_tax'))
                ->first();

            $details['refund_amount'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->where('payment_status', 2)
                ->select('total_amount', DB::raw('SUM(total_amount) as refund_amount'))
                ->first();
        }
        else{

            $details['gross_revenue'] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('total_amount', DB::raw('SUM(total_amount) as total_amount'))
                ->first();


            $details['discount_amount'] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('discount_amount', DB::raw('SUM(discount_amount) as discount_amount'))
                ->first();

            $details['total_tax'] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->select('total_tax', DB::raw('SUM(total_tax) as total_tax'))
                ->first();

            $details['refund_amount'] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->where('payment_status', 2)
                ->select('total_amount', DB::raw('SUM(total_amount) as refund_amount'))
                ->first();
        }

        return response()->json(apiResponseHandler($details, 'Success', 200), 200);
    }

    public function onTopCustomerChange(Request $request)
    {
        $split_date = explode('-', $request->input('full_date'));

        if ($request->filled('full_date')) {
            $start_date = date('Ymd', strtotime($split_date[0]));
            $end_date = date('Ymd', strtotime($split_date[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        if(Session::get('my_restaurant') != 'all'){
            $details['top_customers'] = Order::join('users', 'orders.user_id', 'users.id')
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->where('restaurant_id', '=', Session::get('my_restaurant'))->select('orders.user_id', DB::raw('concat(users.first_name, " " ,users.last_name) as name'), DB::raw('COUNT(DISTINCT orders.order_id) as order_count'), DB::raw('SUM(orders.total_amount) as total_amount'), DB::raw('MAX(DATE_FORMAT(orders.created_at,"%m/%d/%Y")) as last_order_at'))->groupBy('orders.user_id')->orderBy('total_amount', 'DESC')->limit(8)->get();

        }
        else{
            $details['top_customers'] = Order::join('users', 'orders.user_id', 'users.id')
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })->select('orders.user_id', DB::raw('concat(users.first_name, " " ,users.last_name) as name'), DB::raw('COUNT(DISTINCT orders.order_id) as order_count'), DB::raw('SUM(orders.total_amount) as total_amount'), DB::raw('MAX(DATE_FORMAT(orders.created_at,"%m/%d/%Y")) as last_order_at'))->groupBy('orders.user_id')->orderBy('total_amount', 'DESC')->limit(8)->get();

        }

        return response()->json(apiResponseHandler($details, 'Success', 200), 200);
    }

    public function getOrderPickupTiming(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $response = null;

        if ($request->input('sales_type') == 1) { //Sales basis of last week
            $year = date('Y');
            $lastWeekNo = date('W') - 1;
            $start_date = (new DateTime())->setISODate($year, $lastWeekNo)->format('Y-m-d');
            $end_date = (new DateTime())->setISODate($year, $lastWeekNo, 7)->format('Y-m-d');
            if(Session::get('my_restaurant') != 'all'){
                $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('15:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
            else{
                $response['seven_to_nine'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('15:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y-%m-%d')"), array($start_date, $end_date))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }

        }
        if ($request->input('sales_type') == 2) { //Sales basis of last month

            $lastMonth = Carbon::now()->subMonth()->timestamp;

            if(Session::get('my_restaurant') != 'all'){
                $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
            else{
                $response['seven_to_nine'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where('orders.pickup_time', '>', $lastMonth)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
        }
        if ($request->input('sales_type') == 3) { //Sales basis of last month
            $currentYear = Carbon::now()->year;

            if(Session::get('my_restaurant') != 'all'){
                $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
            else{
                $response['seven_to_nine'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time),'%Y')"), '=', $currentYear)
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
        }
        if ($request->input('sales_type') == 4) { //Sales basis of All year
            if(Session::get('my_restaurant') != 'all'){
                $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
            else{
                $response['seven_to_nine'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());
                $response['nine_to_eleven'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());
                $response['thirteen_to_fifteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
        }
        if ($request->input('sales_type') == 0) { //sales on the basis of today

            if(Session::get('my_restaurant') != 'all'){
                $response['seven_to_nine'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());

                $response['nine_to_eleven'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());

                $response['thirteen_to_fifteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
            else{
                $response['seven_to_nine'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('07:00', '08:59'))
                    ->get());

                $response['nine_to_eleven'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('09:00', '10:59'))
                    ->get());
                $response['eleven_to_thirteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('11:00', '12:59'))
                    ->get());

                $response['thirteen_to_fifteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('13:00', '14:59'))
                    ->get());
                $response['fifteen_to_seventeen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('16:00', '16:59'))
                    ->get());
                $response['seventeen_to_nineteen'] = count(Order::where(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
                    ->whereBetween(DB::raw("DATE_FORMAT(FROM_UNIXTIME(orders.pickup_time), '%H:%i %p')"), array('17:00', '18:59'))
                    ->get());
            }
        }

        return response()->json(apiResponseHandler($response));
    }


    public function getOrderTiming(Request $request)
    {

//        $validator = Validator::make($request->all(), [
//            'sales_type' => 'required'
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
//        }

        $response = null;
//
//        if ($request->input('sales_type') == 1) { //Sales basis of last week
//            $year = date('Y');
//            $lastWeekNo = date('W') - 1;
//            $start_date = (new DateTime())->setISODate($year, $lastWeekNo)->format('Y-m-d');
//            $end_date = (new DateTime())->setISODate($year, $lastWeekNo, 7)->format('Y-m-d');
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
//                ->get());
//            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
//                ->get());
//            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
//                ->get());
//            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('15:00', '16:59'))
//                ->get());
//            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
//                ->get());
//            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
//                ->get());
//
//            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
//                ->get());
//            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->where(function ($query) {
//                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
//                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
//                })->get());
//
//            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
//                ->get());
//            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
//                ->get());
//            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
//                ->get());
//
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y-%m-%d')"), array($start_date, $end_date))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//
//        }
//        if ($request->input('sales_type') == 2) { //Sales basis of last month
//            $lastMonthName = Date('Ym');
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
//                ->get());
//            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
//                ->get());
//            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
//                ->get());
//            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
//                ->get());
//            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
//                ->get());
//            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
//                ->get());
//            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
//                ->get());
//            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->where(function ($query) {
//                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
//                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
//                })->get());
//
//            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
//                ->get());
//            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
//                ->get());
//            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where('orders.created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 1 MONTH)'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
//                ->get());
//        }
//        if ($request->input('sales_type') == 3) { //Sales basis of current_year
//            $current_year = date('Y');
//
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
//                ->get());
//            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
//                ->get());
//            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
//                ->get());
//            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
//                ->get());
//            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
//                ->get());
//            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
//                ->get());
//            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
//                ->get());
//            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->where(function ($query) {
//                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
//                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
//                })->get());
//
//            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
//                ->get());
//            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
//                ->get());
//            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw('YEAR(orders.created_at)'), '=', $current_year)
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
//                ->get());
//        }
//        if ($request->input('sales_type') == 4) { //Sales basis of all year
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
//                ->get());
//            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
//                ->get());
//            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
//                ->get());
//            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
//                ->get());
//            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
//                ->get());
//            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
//                ->get());
//            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
//                ->get());
//            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(function ($query) {
//                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
//                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
//                })->get());
//
//            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
//                ->get());
//            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
//                ->get());
//            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
//                ->get());
//        }
//        if ($request->input('sales_type') == 0) {
//            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
//                ->get());
//            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
//                ->get());
//            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
//                ->get());
//            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
//                ->get());
//            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
//                ->get());
//            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
//                ->get());
//            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
//                ->get());
//            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
//                ->get());
//            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->where(function ($query) {
//                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
//                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
//                })->get());
//
//            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
//                ->get());
//            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
//                ->get());
//            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
//                ->where(DB::raw("DATE_FORMAT(created_at,'%Y%c%d')"), '=', DB::raw("DATE_FORMAT(now(),'%Y%c%d')"))
//                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
//                ->get());
//        }


        $split_date = explode('-', $request->input('full_date'));

        if ($request->filled('full_date')) {
            $start_date = date('Ymd', strtotime($split_date[0]));
            $end_date = date('Ymd', strtotime($split_date[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        if(Session::get('my_restaurant') != 'all'){
            $response['seven_to_nine'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
                ->get());

            $response['nine_to_eleven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
                ->get());
            $response['eleven_to_thirteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
                ->get());
            $response['thirteen_to_fifteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
                ->get());
            $response['fifteen_to_seventeen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
                ->get());
            $response['seventeen_to_nineteen'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
                ->get());
            $response['nineteen_to_twentyone'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
                ->get());
            $response['twentyone_to_twentythree'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
                ->get());
            $response['twentythree_to_one'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->where(function ($query) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
                })->get());

            $response['one_to_three'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
                ->get());
            $response['three_to_five'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
                ->get());
            $response['five_to_seven'] = count(Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
                ->get());
        }
        else{
            $response['seven_to_nine'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('07:00', '08:59'))
                ->get());

            $response['nine_to_eleven'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('09:00', '10:59'))
                ->get());
            $response['eleven_to_thirteen'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('11:00', '12:59'))
                ->get());
            $response['thirteen_to_fifteen'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('13:00', '14:59'))
                ->get());
            $response['fifteen_to_seventeen'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('16:00', '16:59'))
                ->get());
            $response['seventeen_to_nineteen'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('17:00', '18:59'))
                ->get());
            $response['nineteen_to_twentyone'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('19:00', '20:59'))
                ->get());
            $response['twentyone_to_twentythree'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('21:00', '22:59'))
                ->get());
            $response['twentythree_to_one'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->where(function ($query) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('23:00', '23:59'))
                        ->orWhere(DB::raw("DATE_FORMAT(orders.created_at,'%H')"), '=', '00');
                })->get());

            $response['one_to_three'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('01:00', '02:59'))
                ->get());
            $response['three_to_five'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('03:00', '04:59'))
                ->get());
            $response['five_to_seven'] = count(Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%H:%i %p')"), array('05:00', '06:59'))
                ->get());
        }

        return response()->json(apiResponseHandler($response));
    }

    public function noOfOrders(Request $request)
    {
        $response = null;

        $split_date = explode('-', $request->input('full_date'));
        if ($request->filled('full_date')) {

            $start_date = date('Ymd', strtotime($split_date[0]));
            $end_date = date('Ymd', strtotime($split_date[1]));
        } else {

            $start_date = null;
            $end_date = null;
        }

        if(Session::get('my_restaurant') != 'all'){
            $response = Order::select(DB::raw('count(order_details.item_name) as count'), 'order_details.item_name')->leftJoin('order_details', 'order_details.order_id', 'orders.order_id')
                ->where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy('order_details.item_name')
                ->get();
        }
        else{
            $response = Order::select(DB::raw('count(order_details.item_name) as count'), 'order_details.item_name')->leftJoin('order_details', 'order_details.order_id', 'orders.order_id')
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy('order_details.item_name')
                ->get();
        }

        return response()->json(apiResponseHandler($response));
    }

    public function getSales(Request $request)
    {

        $response = null;

        $split_date = explode('-', $request->input('full_date'));
        if ($request->filled('full_date')) {
            $start_date = date('Ymd', strtotime($split_date[0]));
            $end_date = date('Ymd', strtotime($split_date[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }

        if(Session::get('my_restaurant') != 'all'){
            $response['series'][0] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('order_id', DB::raw('COUNT(order_id) as order_id'))
                ->pluck('order_id');

            $response['series'][1] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('total_amount', DB::raw('ROUND(SUM(total_amount),2) as total_amount'))
                ->pluck('total_amount');

            $response['type'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('created_at', DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y') as date"))
                ->pluck('date');

            $te_year = date('Y');
            $te_lastWeekNo = date('W') - 1;
            $te_start_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo)->format('d-m-Y');
            $te_end_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo, 7)->format('d-m-Y');
            $te_lastMonthName = Date('Ym');

            $response['te_weekly'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                ->select(DB::raw('ROUND(SUM(total_amount),2) as earned'))->first();

            $response['te_monthly'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                ->select(DB::raw('ROUND(SUM(total_amount),2) as earned'))
                ->first();

            $response['te_yearly'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('created_at', '<', DB::raw('Now()'))
                ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                ->select('total_amount', DB::raw('ROUND(SUM(total_amount),2) as earned'))
                ->first();
        }
        else{
            $response['series'][0] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('order_id', DB::raw('COUNT(order_id) as order_id'))
                ->pluck('order_id');

            $response['series'][1] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('total_amount', DB::raw('ROUND(SUM(total_amount),2) as total_amount'))
                ->pluck('total_amount');

            $response['type'] = Order::when(($start_date && $end_date), function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(orders.created_at,'%Y%m%d')"), array($start_date, $end_date));
                })
                ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%d-%m-%Y\')'))
                ->select('created_at', DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y') as date"))
                ->pluck('date');

            $te_year = date('Y');
            $te_lastWeekNo = date('W') - 1;
            $te_start_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo)->format('d-m-Y');
            $te_end_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo, 7)->format('d-m-Y');
            $te_lastMonthName = Date('Ym');

            $response['te_weekly'] = Order::whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                ->select(DB::raw('ROUND(SUM(total_amount),2) as earned'))->first();

            $response['te_monthly'] = Order::where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                ->select(DB::raw('ROUND(SUM(total_amount),2) as earned'))
                ->first();

            $response['te_yearly'] = Order::where('created_at', '<', DB::raw('Now()'))
                ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                ->select('total_amount', DB::raw('ROUND(SUM(total_amount),2) as earned'))
                ->first();
        }

        return response()->json(apiResponseHandler($response));
    }

    public function ordersPerItem(Request $request)
    {
        $filter = $request->input('filter');

        $response = OrderDetail::query()->with(['item'])->leftJoin('orders', 'orders.order_id', '=', 'order_details.order_id');

        if(Session::get('my_restaurant') != 'all'){
            $response->where('orders.restaurant_id', '=', Session::get('my_restaurant'));
        }

        if ($filter == 1) {
            $response->whereDate('orders.created_at', Carbon::today());     //today
        } elseif ($filter == 2) {
            $response->whereDate('orders.created_at', Carbon::yesterday());  //yesterday
        } elseif ($filter == 3) {
            $date = Carbon::today()->subDays(7);
            $response->whereDate('orders.created_at', '>=', $date); //last week

        } elseif ($filter == 4) {
            $response->whereYear('orders.created_at', Carbon::now()->year)
                ->whereMonth('orders.created_at', Carbon::now()->month);       //Current month

        } elseif ($filter == 5) {
            $lastMonthName = Date('Ym');

            $response->where(DB::raw("EXTRACT(YEAR_MONTH FROM orders.created_at)"), '=', $lastMonthName - 1);

        } elseif ($filter == 6) {
            $response->where("orders.created_at", ">", Carbon::now()->subMonths(6)); //last 6 months
        } elseif ($filter == 7) {
            $response->whereYear('orders.created_at', date('Y', strtotime('-1 year'))); //last year
        }

        $response->select(
            'order_details.item_id', DB::raw('COUNT(item_id) as item_count'))
            ->groupBy('item_id')
            ->orderBy('item_count', 'DESC');

        return response()->json(apiResponseHandler($response->get(), 'success', 200), 200);
    }

    public function getEarning(Request $request)
    {
        $earningType = $request->input('earning_type');
        $te_year = date('Y');
        $te_lastWeekNo = date('W') - 1;
        $te_start_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo)->format('d-m-Y');
        $te_end_date = (new DateTime())->setISODate($te_year, $te_lastWeekNo, 7)->format('d-m-Y');
        $te_lastMonthName = Date('Ym');
        $response = null;

        if(Session::get('my_restaurant') != 'all'){
            $selectedResLunch = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('menu_name', '=', 'Lunch')
                ->first()->menu_id;
            $selectedResBreakfast = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('menu_name', '=', 'Breakfast')
                ->first()->menu_id;

            if ($earningType == 1) {
                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResBreakfast)
                    ->whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))->first();

            } else if ($earningType == 2) {

                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResBreakfast)
                    ->where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

            } else if ($earningType == 3) {

                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where('created_at', '<', DB::raw('Now()'))
                    ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                    ->select('total_amount', DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where('created_at', '<', DB::raw('Now()'))
                    ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                    ->select('total_amount', DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();
            }
        }else{
            $selectedResLunch = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('menu_name', '=', 'Lunch')
                ->first()->menu_id;
            $selectedResBreakfast = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))
                ->where('menu_name', '=', 'Breakfast')
                ->first()->menu_id;

            if ($earningType == 1) {
                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResBreakfast)
                    ->whereBetween(DB::raw("DATE_FORMAT(created_at,'%d-%m-%Y')"), array($te_start_date, $te_end_date))
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))->first();

            } else if ($earningType == 2) {

                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResBreakfast)
                    ->where(DB::raw("EXTRACT(YEAR_MONTH FROM created_at)"), '=', $te_lastMonthName - 1)
                    ->select(DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

            } else if ($earningType == 3) {

                $response['lunch'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where('created_at', '<', DB::raw('Now()'))
                    ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                    ->select('total_amount', DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();

                $response['breakfast'] = Order::where('restaurant_id', '=', Session::get('my_restaurant'))
                    ->where('menu_id', '=', $selectedResLunch)
                    ->where('created_at', '<', DB::raw('Now()'))
                    ->orWhere('created_at', '>', DB::raw('DATE_ADD(Now(), INTERVAL- 12 MONTH)'))
                    ->select('total_amount', DB::raw('ROUND(SUM(total_amount)) as earned'))
                    ->first();
            }
        }

        return response()->json(apiResponseHandler($response));
    }

    public function loadPrepTime()
    {
        return view('dashboard.preparation-time', ['response' => Restaurant::where('id', '=', Session::get('my_restaurant'))->get()]);
    }

    public function addLocationPreparationTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preparation_time' => 'requiredIf:flag,1',
            'pickup_time' => 'requiredIf:flag,2',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        Restaurant::where('id', '=', Session::get('my_restaurant'))
            ->update(
                [
                    'preparation_time' => $request->input('preparation_time') * 60,
                    'pickup_time' => $request->input('pickup_time') * 60,
                ]
            );
        return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
    }

    public function orderPreparationTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'preparation_time' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $orderDetails = Order::where('order_id', '=', $request->input('order_id'))->first();
        $preparationTime = $orderDetails['pickup_time'] + $request->input('preparation_time') * 60;
        $preparationTime1 = $orderDetails['preparation_time'] + $request->input('preparation_time') * 60;

        Order::where('order_id', '=', $request->input('order_id'))->update([
            'pickup_time' => $preparationTime,
            'preparation_time' => $preparationTime1
        ]);

        $user = User::where('id', '=', $orderDetails['user_id'])->get();

        /*Email Notification*/
        $template = view('email-templates.order-preparing-increased', [
            'name' => $orderDetails['user_first_name'],
            'preparation_time' => date('h:i a', $preparationTime),
        ])->render();

        if (count($user)) {
            $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $orderDetails['user_id'])->first();
            if ($subscriptionPreference['email_subscription']) {
                sendEmail($template, $orderDetails['user_email'], 'Order Delay: Falafel Corner');
            }
        } else {
            sendEmail($template, $orderDetails['user_email'], 'Order Delay: Falafel Corner');
        }

        /*Push Notification*/
        if (count($user)) {
            $devicePreference = DevicePreference::where('user_id', '=', $orderDetails['user_id'])->first();
            $notification = array(
                'title' => 'Order Delay',
                'body' => 'We are running a little behind on your order. We apologize for the inconvenience. Rest assured, we\'re working hard to get it to you as soon as possible.' . ' New pickup time is ' . date('h:i a', $preparationTime) . '. Click here to review your order details.',
            );
            if ($devicePreference['push_notification']) {
                $token = FirebaseToken::where('user_id', '=', $orderDetails['user_id'])->get();
                foreach ($token as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 3, 'order_id' => $request->input('order_id')]);
                }
            }
        } else {
            $notification = array(
                'title' => 'Order Delay',
                'body' => 'We are running a little behind on your order. We apologize for the inconvenience. Rest assured, we\'re working hard to get it to you as soon as possible.' . ' New pickup time is ' . date('h:i a', $preparationTime) . '. Click here to review your order details.',
            );
            app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$orderDetails['firebase_token']], ['notification_type' => 3, 'order_id' => $request->input('order_id')]);
        }

        return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
    }

    public function getCategoriesItems($categoryId)
    {
        $response = ItemCategory::where('category_id', '=', $categoryId)
            ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
            ->get();

        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function addAdminReward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'users' => 'required|array',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $adminReward = null;
        $expiry = $request->input('expiry');
        if ($request->input('type') == 1) {
            if (!$request->input('item_id')) {
                $categoryItem = ItemCategory::where('category_id', '=', $request->input('category_id'))
                    ->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                    ->get();

                foreach ($categoryItem as $item) {
                    $adminReward = new AdminReward();
                    $adminReward->name = $request->input('name');
                    $adminReward->description = $request->input('description');
                    $adminReward->expiry = strtotime(date('d-m-Y', strtotime("+" . $expiry . "days")));
                    $adminReward->item_id = $item['item_id'];
                    $adminReward->unique_key = time();
                    $adminReward->save();
                }
            } else {
                $adminReward = new AdminReward();
                $adminReward->name = $request->input('name');
                $adminReward->description = $request->input('description');
                $adminReward->expiry = strtotime(date('d-m-Y', strtotime("+" . $expiry . "days")));
                $adminReward->item_id = $request->input('item_id');
                $adminReward->unique_key = time();
                $adminReward->save();
            }

            foreach ($request->input('users') as $item) {
                RewardCoupon::create([
                    'user_id' => $item,
                    'expiry' => strtotime(date('d-m-Y', strtotime("+" . $expiry . "days"))),
                    'coupon_type' => 3,
                    'status' => 1,
                    'admin_reward_id' => $adminReward->admin_reward_id,
                ]);
            }

        } elseif ($request->input('type') == 2) {
//            $adminReward = new AdminReward();
//            $adminReward->name = $request->input('name');
//            $adminReward->description = $request->input('description');
//            $adminReward->expiry = strtotime(date('d-m-Y', strtotime("+" . $expiry . "days")));
//            $adminReward->reward_point = $request->input('reward_points');
//            $adminReward->unique_key = time();
//            $adminReward->save();
        }

        $text = $request->input('description');
        $notification = array(
            'message' => $request->input('description'),
            'title' => $request->input('name'),
            'body' => $request->input('description'),
            'type' => 7,
            'data' => (object)array(),
            'sound' => 'default'
        );

        foreach ($request->input('users') as $item) {
            if ($request->input('type') == 1) {
                $assignReward = new AssignAdminReward();
                $assignReward->admin_reward_id = $adminReward->admin_reward_id;
                $assignReward->user_id = $item;
                $assignReward->save();
            }

            if ($request->input('type') == 2) {
                UserRewards::create([
                    'order_id' => 0,
                    'user_id' => $item,
                    'total_rewards' => $request->input('reward_points'),
                    'month' => strtotime(date('Y-m', time()) . '-1'),
                    'type' => 4
                ]);
            }

            /*Email Notification*/
            $user = User::where('id', '=', $item)->first();
            $template = view('email-templates.admin-reward',
                [
                    'name' => $user->first_name,
                    'header_text' => $request->input('description'),
                ])->render();
            $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $user->id)->first();
            if ($subscriptionPreference['email_subscription']) {
                sendEmail($template, $user->email, 'You\'ve Just Been Rewarded: Falafel Corner');
            }

            $devicePreference = DevicePreference::where('user_id', $user->id)->first();
            $tokens = FirebaseToken::where('user_id', '=', $user->id)->get();
            if ($devicePreference['push_notification']) {
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['message'], [$tk['token']], ['notification_type' => 7]);
                }
            }
        }

        /*Push Notification*/

        $receivers = FirebaseToken::whereIn('user_id', $request->input('users'))->get();
        app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);

        return response()->json(apiResponseHandler([], 'success', 200), 200);
    }

    public function deleteAdminReward($rewardId)
    {
        if ($rewardId) {
            AdminReward::where('admin_reward_id', '=', $rewardId)->delete();
            AssignAdminReward::where('admin_reward_id', '=', $rewardId)->delete();
            return response()->json(apiResponseHandler([], 'Reward Deleted Successfully', 200), 200);
        } else {
            return response()->json(apiResponseHandler([], 'Not Found', 400), 400);
        }

    }

    public function loadBonus()
    {
        $menuTypes = Menu::where('restaurant_id', '=', Session::get('my_restaurant'))->get();
        $allBonus = Bonus::with(['appliedFor' => function ($query) {
            $query->leftJoin('users', 'users.id', 'bonus_applied_for.user_id')
                ->select('bonus_applied_for.bonus_id',
                    'users.first_name',
                    'users.last_name');
        }])->orderBy('created_at', 'desc')->get();
        return view('dashboard.bonus', ['menu_types' => $menuTypes, 'all_bonus' => $allBonus]);
    }

    public function loadUserNotification()
    {
        return view('dashboard.send-notification');
    }

    public function createBonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bonus_name' => 'required|unique:bonus|string',
            'bonus_type' => 'required',
            'bonus_expiry' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }
        $eligibleUsers = [];
        if ($request->input('bonus_user_type') == 1) {
            $eligibleUsers = User::select('id')->pluck('id');
        } elseif ($request->input('bonus_user_type') == 2) {
            $eligibleUsers = User::select('id')->whereIn('id', $request->input('selected_users'))->pluck('id');
        } elseif ($request->input('bonus_condition_type')) {
            $eligibleUsers = $this->getBonusUsers($request->input('bonus_condition_type'), $request);
        }
        if (count($eligibleUsers) == 0) {
            return response()->json(apiResponseHandler([], 'Please select User(s) Type or Bonus Condition', 400), 400);
        }
        //return response()->json(apiResponseHandler([$eligibleUsers], 'No user found for this bonus conditions', 400), 400);

        $createBonus = new Bonus();
        $createBonus->bonus_name = $request->input('bonus_name');
        $createBonus->bonus_type = $request->input('bonus_type');
        $createBonus->bonus_expiry = $request->input('bonus_expiry');
        $createBonus->bonus_condition_type = $request->input('bonus_condition_type') ? $request->input('bonus_condition_type') : 0;
        $createBonus->notification_text = $request->input('notification_text');
        $createBonus->description = $request->input('description');
        $createBonus->term_and_condition = $request->input('term_and_condition');
        $createBonus->bonus_free_item_id = $request->input('bonus_free_item_id');
        $createBonus->bonus_extra_point = $request->input('bonus_extra_point');
        $createBonus->bonus_points_multiplier = $request->input('bonus_points_multiplier');
        $createBonus->bonus_discount = $request->input('bonus_discount');
        $createBonus->bonus_orders_no = $request->input('bonus_orders_no');
        $createBonus->bonus_start_date = $request->input('bonus_start_date');
        $createBonus->bonus_end_date = $request->input('bonus_end_date');
        $createBonus->bonus_start_hour = $request->input('bonus_start_hour');
        $createBonus->bonus_end_hour = $request->input('bonus_end_hour');
        $createBonus->bonus_plates_no = $request->input('bonus_plates_no');
        $createBonus->bonus_user_points = $request->input('bonus_user_points');
        $createBonus->save();

        $notification = array(
            'message' => $request->input('notification_text'),
            'title' => 'Admin : ' . '  ' . $request->input('notification_text'),
            'body' => $request->input('notification_text'),
            'type' => 12,
            'data' => (object)array(),
            'sound' => 'default'
        );
        foreach ($eligibleUsers as $applied) {
            $bonusAppliedFor = new BonusAppliedFor();
            $bonusAppliedFor->bonus_id = $createBonus->bonus_id;
            $bonusAppliedFor->user_id = $applied;
            $bonusAppliedFor->save();
            $this->bonusPushNotification($applied, $notification);
            $user = User::where('id', '=', $applied)->first();
            $template = view('email-templates.admin-reward',
                [
                    'name' => $user->first_name,
                    'header_text' => $request->input('notification_text'),
                ])->render();
            $subscriptionPreference = SubscriptionPreference::where('user_id', '=', $user->id)->first();
            if ($subscriptionPreference['email_subscription']) {
                sendEmail($template, $user->email, 'Falafel Corner offer.');
            }
        }

        if ($request->input('bonus_type') == 1) {
            foreach ($eligibleUsers as $applied) {
                $userReward = new UserRewards();
                $userReward->order_id = 0;
                $userReward->user_id = $applied;
                $userReward->total_rewards = $request->input('bonus_extra_point');
                $userReward->type = 4;
                $userReward->month = strtotime(date('Y-m', time()) . '-1');
                $userReward->save();
                $this->bonusPushNotification($applied, $notification);
            }
        }

        $receivers = FirebaseToken::whereIn('user_id', $eligibleUsers)->get();
        app('App\Http\Controllers\Admin\OrderController')->storePushNotifications($receivers, $notification);

        return response()->json(apiResponseHandler([], 'success', 200), 200);
    }

    public function bonusPushNotification($applied, $notification)
    {
        /*Push Notification*/
        $devicePreference = DevicePreference::where('user_id', '=', $applied)->first();
        if ($devicePreference['push_notification']) {
            if ($applied != '48') {
                $tokens = FirebaseToken::where('user_id', '=', $applied)->get();
                foreach ($tokens as $tk) {
                    app('App\Http\Controllers\RealTimeController')->cloudNotifications($notification['title'], $notification['body'], [$tk['token']], ['notification_type' => 12]);
                }
            }
        }
    }

    public function searchCategories(Request $request)
    {
        $allMenus = Menu::with(['categories' => function ($query) {
            $query->leftJoin('categories', 'categories.category_id', 'menu_categories.category_id')
                ->whereNotNull('categories.category_id')
                ->select('menu_categories.menu_id', 'menu_categories.category_id',
                    'menu_categories.category_id', 'menu_categories.order_no',
                    'categories.category_name','categories.clover_id')
                ->orderBy('menu_categories.order_no');
        }])->where('restaurant_id', '=', Session::get('my_restaurant'))
            ->get();

        foreach ($allMenus as $value) {
            foreach ($value['categories'] as $val) {
                $singleCategory = Category::with(['sideMenu' => function ($query) {
                    $query->leftJoin('items', 'items.item_id', 'item_categories.item_id')
                        ->whereNotNull('items.item_id');
                }])->where('categories.category_id', '=', $val['category_id'])->first();
                $val['items'] = $singleCategory['sideMenu'] ? $singleCategory['sideMenu'] : [];
            }
        }

        return response()->json(apiResponseHandler($allMenus, 'success', 200), 200);
    }

    public function searchModifiers(Request $request)
    {
        $searchModifier = ModifierGroup::query();
        $keyword = $request->input('keyword');

        $searchModifier->with(['items' => function ($query) {
            $query->leftJoin('items', 'items.item_id', 'modifier_items.item_id')
                ->whereNotNull('items.item_id');
        }])->where('modifier_groups.restaurant_id', '=', Session::get('my_restaurant'));

        if ($request->input('keyword')) {
            $searchModifier->where(function ($query) use ($keyword) {
                $query->where('modifier_groups.modifier_group_name', 'like', '%' . $keyword . '%');
            });
        }

        $limit = getPaginationLimit();
        $response = $searchModifier->orderBy('modifier_groups.order_no', 'ASC')->get();
        return response()->json(apiResponseHandler($response, 'success', 200, $limit), 200);

    }

    public function singleModifier($modifierId)
    {
        $response = ModifierGroup::with(['items' => function ($query) {
            $query->where('added_from', 2);
        }])->where('modifier_group_id', '=', $modifierId)->first();
        $response->is_rule = 0;
        if ($response->item_exactly) {
            $response->is_rule = 1;
        } elseif ($response->item_range_from && $response->item_range_to) {
            $response->is_rule = 2;
        } elseif ($response->item_maximum) {
            $response->is_rule = 3;
        }
        return response()->json(apiResponseHandler($response, 'success', 200), 200);
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
                ->where(DB::raw("DATE_FORMAT(orders.created_at,'%h:%i %p')"), array($request->input('bonus_start_hour')));
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
            $order = $order->having('count', '=', $request->input('order_index_number') - 1);
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
    }

    function getBonusUser(Request $request)
    {
        $response = null;
        $allUsers = null;
        if ($request->input('user_type') == 1) {

            $allUsers = User::query();
            $allUsers = $allUsers->pluck('id');

        } else if ($request->input('user_type') == 2) {

            $allUsers = Order::select('user_id', 'order_total',
                DB::raw('DATE_FORMAT(created_at,\'%Y-%d-%m\') as order_date'),
                DB::raw('SUM(order_total) as total')
            )->whereBetween(DB::raw('DATE_FORMAT(created_at,\'%Y-%d-%m\')'),
                array($request->input('start_time_frame'), $request->input('end_time_frame')))
                ->having('total', '>', $request->input('user_amount'))
                ->groupBy('user_id')
                ->pluck('user_id');

        } else if ($request->input('user_type') == 3) {

            $allUsers = Order::select('user_id', 'order_total',
                DB::raw('DATE_FORMAT(created_at,\'%Y-%d-%m\') as order_date'),
                DB::raw('SUM(order_total) as total')
            )->whereBetween(DB::raw('DATE_FORMAT(created_at,\'%Y-%d-%m\')'), array($request->input('start_time_frame'), $request->input('end_time_frame')))
                ->having('total', '<', $request->input('user_amount'))
                ->groupBy('user_id')
                ->pluck('user_id');

        } else if ($request->input('user_type') == 4) {

            $allUsers = Order::select('user_id', 'order_total', 'created_at')
                ->whereBetween(DB::raw('DATE_FORMAT(created_at,\'%Y-%d-%m\')'), array($request->input('start_time_frame'), $request->input('end_time_frame')))
                ->groupBy('user_id')
                ->pluck('user_id');

        }

        $response['records'] = Order::query();
        $response['records']->leftJoin('user_rewards', 'user_rewards.user_id', 'orders.user_id')
            ->select('orders.order_id',
                'orders.order_total',
                'orders.pickup_time',
                'orders.preparation_time',
                'user_rewards.total_rewards',
                'orders.reference_id',
                'orders.status',
                'orders.total_amount',
                'orders.total_tax',
                DB::raw('DATE_FORMAT(orders.created_at,\'%Y-%m-%d %h:%i %p\') as order_date'),
                DB::raw('COUNT(orders.user_id) as count'),
                DB::raw('SUM(user_rewards.total_rewards) as total_rewards'),
                'orders.user_id')
            ->whereIn('orders.user_id', $allUsers);

        if ($request->input('bonus_condition') == 1) {
            $response['records']->having('count', '>=', $request->input('no_of_order'));
        }

        if ($request->input('bonus_condition') == 2) {
            $response['records']->where(DB::raw('DATE_FORMAT(orders.created_at,\'%Y-%m-%d\')'), '=', $request->input('order_date'));
        }

        if ($request->input('bonus_condition') == 3) {
            $response['records']->where(DB::raw('DATE_FORMAT(orders.created_at,\'%Y-%m-%d %H:%i\')'), '=', $request->input('order_date') . ' ' . $request->input('order_time'));
        }

        if ($request->input('bonus_condition') == 4) {
            $response['records']->whereBetween(DB::raw('DATE_FORMAT(orders.created_at,\'%Y-%m-%d\')'), array($request->input('start_date'), $request->input('end_date')));
        }

        if ($request->input('bonus_condition') == 5) {
            $response['records']->having('count', '>=', $request->input('no_of_order'))
                ->whereBetween(DB::raw('DATE_FORMAT(orders.created_at,\'%Y-%m-%d\')'), array($request->input('start_date'), $request->input('end_date')));
        }

        if ($request->input('bonus_condition') == 7) {
            $response['records']->having('user_rewards.total_rewards', '>=', $request->input('user_no_of_points'));
        }

        $response['records'] = $response['records']->groupBy('orders.user_id')
            ->pluck('orders.user_id');

        $response['details'] = User::whereIn('id', $response['records'])
            ->get();
        return response()->json(apiResponseHandler($response, 'success', 200), 200);
    }

    public function addCompleteMeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $categoryItem = ItemCategory::where('category_id', '=', $request->input('category_id'))->get();

        $isAlready = CompleteMeals::where('category_id', '=', $request->input('category_id'))
            ->where('menu_id', '=', $request->input('menu_id'))
            ->get();

        if (count($isAlready)) {
            return response()->json(apiResponseHandler([], 'Selected category is already exist.', 400), 400);
        } else {
            if (count($categoryItem)) {
                foreach ($categoryItem as $item) {
                    CompleteMeals::create([
                        'menu_id' => $request->input('menu_id'),
                        'category_id' => $request->input('category_id'),
                        'item_id' => $item['item_id']
                    ]);
                }
                return response()->json(apiResponseHandler([], 'Added Successfully', 200), 200);
            } else {
                return response()->json(apiResponseHandler([], 'No items are available in selected category.', 400), 400);
            }
        }
    }

    public function deleteMeals($menuId)
    {
        CompleteMeals::where('menu_id', '=', $menuId)->delete();
        return response()->json(apiResponseHandler([], 'Deleted Successfully', 200), 200);
    }

    public function updateMeals($itemId, $status)
    {
        CompleteMeals::where('id', '=', $itemId)->update(['is_active' => $status]);
        return response()->json(apiResponseHandler([], 'Updated Successfully', 200), 200);
    }

    public function completeMealItems($menuId)
    {
        $response = CompleteMeals::leftJoin('items', 'items.item_id', 'complete_meals.item_id')
            ->where('menu_id', '=', $menuId)->get();
        $html = "<tr><th>Item Name</th><th>Action</th></tr>";

        foreach ($response as $item) {
            if ($item->is_active) {
                $html .= '<tr><td>' . $item->item_name . '</td><td><a href="javascript:void(0)" id="status-' . $item->id . '" onclick="deactivateMeal(' . $menuId . ',' . $item->id . ',0)">Deactivate</a></td></tr>';
            } else {
                $html .= '<tr><td>' . $item->item_name . '</td><td><a href="javascript:void(0)" id="status-' . $item->id . '" onclick="deactivateMeal(' . $menuId . ',' . $item->id . ',1)">Activate</a></td></tr>';
            }
        }
        return response()->json(apiResponseHandler($html, 'All items', 200), 200);
    }

    public function getSingleBonus($bonusId)
    {
        $response = Bonus::with(['appliedFor' => function ($query) {
            $query->leftJoin('users', 'users.id', 'bonus_applied_for.user_id')
                ->select('bonus_applied_for.bonus_id',
                    'users.first_name',
                    'users.last_name');
        }])->where('bonus_id', '=', $bonusId)->first();
        return response()->json(apiResponseHandler($response, 'Success', 200), 200);
    }

    public function updateRowOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required',
            'select_rows' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $selectRows = $request->input('select_rows');
        $table = $request->input('table');

        if ($table == 'items') {
            foreach ($selectRows as $row) {
                DB::table('items')->where('item_id', '=', $row['item_id'])->update([
                    'order_no' => $row['order']
                ]);
            }
        }
        if ($table == 'modifier_groups') {
            foreach ($selectRows as $row) {
                DB::table('modifier_groups')->where('modifier_group_id', '=', $row['modifier_group_id'])->update([
                    'order_no' => $row['order']
                ]);
            }
        }
        if ($table == 'categories') {
            foreach ($selectRows as $row) {
                DB::table('menu_categories')
                    ->where('category_id', '=', $row['category_id'])
                    ->where('menu_id', '=', $row['menu_id'])
                    ->update(['order_no' => $row['order']]);
            }
        }

        return response()->json(apiResponseHandler([], 'Success', 200), 200);
    }

    public function getUsersByKeyword(Request $request)
    {
        $users = User::select(
            'id',
            DB::raw("CONCAT(`users`.`first_name`,' ',`users`.`last_name`,' (',`users`.`mobile`,')') as text")
        )->where('first_name', 'like', '%' . $request->get('search') . '%')->orWhere('last_name', 'like', '%' . $request->get('search') . '%')->limit(15)->get();
        return response()->json(apiResponseHandler($users, 'Success', 200), 200);
    }

    public function sendBulkNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_title' => 'required',
            'notification_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        if ($request->input('bonus_user_type') == 1) {
            $eligibleUsers = User::select('id')->pluck('id');
        } elseif ($request->input('bonus_user_type') == 2) {
            $eligibleUsers = User::select('id')->whereIn('id', $request->input('selected_users'))->pluck('id');
        } elseif ($request->input('bonus_condition_type')) {
            $eligibleUsers = $this->getBonusUsers($request->input('bonus_condition_type'), $request);
            if (count($eligibleUsers) == 0) {
                return response()->json(apiResponseHandler([], 'No user found for this conditions', 400), 400);
            }
        } else {
            return response()->json(apiResponseHandler([], 'Please Select Users or Apply Condition(s)', 400), 400);
        }

//        Log::debug($eligibleUsers);exit;

        $notification = array(
            'message' => $request->input('notification_title'),
            'title' => $request->input('notification_title'),
            'body' => $request->input('notification_text'),
        );
        foreach ($eligibleUsers as $applied) {
            $this->bonusPushNotification($applied, $notification);
        }

        return response()->json(apiResponseHandler([], 'Notification Sent Successfully', 200), 200);
    }

    public function syncClover($type){
        $restaurant = Restaurant::where('id', Session::get('my_restaurant'))->first();
        $client = new CloverController($restaurant->clover_mid);
        $client->fetchData($type);

        return response()->json('success', 200);
    }
}
