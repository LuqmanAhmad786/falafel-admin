<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\ModifierGroup;
use App\Models\ModifierGroupRelations;
use App\Models\ModifierItems;
use App\Models\Restaurant;
use Illuminate\Console\Command;

class CopyMenuToLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:copy';

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
        //$allRestaurants = Restaurant::where('id','!=',1)->get();

        $allRestaurants = [8,14,15,16,17,18,19,20,21,22,23];
        foreach ($allRestaurants AS $restaurant){
            // COPY CATEGORIES
            $this->copyCategories(4,$restaurant);

            //COPY MENUS
            $this->copyMenuCategories(4,$restaurant);

            // COPY ITEMS
            $this->copyItems(4,$restaurant);

            // COPY MODIFIERS
            $this->copyModifiers(4,$restaurant);
        }
    }

    public function copyCategories($from, $to){
        $categories = Category::where('restaurant_id', '=', $from)->get();
        foreach ($categories as $val) {
            Category::create([
                'restaurant_id' => $to,
                'category_name' => $val['category_name']
            ]);
        }
    }

//    public function generateMenuTimings($menuId)
//    {
//        Restaurant\RestaurantMenuTiming::insert([
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'monday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'tuesday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'wednesday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'thursday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'friday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'saturday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ],
//            [
//                'restaurant_menu_id' => $menuId,
//                'day' => 'sunday',
//                'from_1' => '07:00:00',
//                'to_1' => '10:00:00',
//                'from_2' => '11:00:00',
//                'to_2' => '8:00:00',
//            ]
//        ]);
//    }

    public function copyMenuCategories($from,$to){
        /*copy menus*/
        $menus = Menu::where('restaurant_id', '=', $from)->first();
        $menuNew = Menu::where('restaurant_id', '=', $to)->first();

        $menuCategory = MenuCategory::where('menu_id', '=', $menus['menu_id'])->get();
        foreach ($menuCategory as $v) {
            $category = Category::find($v['category_id']);
            $newCategory = Category::where('category_name', '=', $category['category_name'])
                ->where('restaurant_id', '=', $to)
                ->first();
            if ($newCategory['category_id']) {
                MenuCategory::create([
                    'menu_id' => $menuNew['menu_id'],
                    'category_id' => $newCategory['category_id']
                ]);
            }
        }
    }

    public function copyItems($from, $to){
        /*copy items*/
        $items = Item::where('restaurant_id', '=', $from)->get();
        foreach ($items as $val) {
            $entry = new Item();
            $entry->restaurant_id = $to;
            $entry->item_name = $val['item_name'];
            $entry->item_price = $val['item_price'];
            $entry->tax_rate = $val['tax_rate'];
            $entry->item_image = $val['item_image'];
            $entry->item_thumbnail = $val['item_thumbnail'];
            $entry->item_description = $val['item_description'];
            $entry->its_own = $val['its_own'];
            $entry->order_no = $val['order_no'];
            $entry->complete_meal_of = $val['complete_meal_of'];
            $entry->restaurant_id = $to;
            $entry->item_image_single = $val['item_image_single'];
            $entry->is_common = $val['is_common'];
            $entry->is_in_stock = $val['is_in_stock'];
            $entry->menu_type = $val['menu_type'];
            $entry->tax_applicable = $val['tax_applicable'];
            $entry->reference_item_id = $val['item_id'];
            $entry->save();

            $itemCategories = ItemCategory::where('item_id', '=', $val['item_id'])->get();
            foreach ($itemCategories as $v) {

                $category = Category::find($v['category_id']);
                $newCategory = Category::where('category_name', '=', $category['category_name'])
                    ->where('restaurant_id', '=', $to)
                    ->first();

                if ($newCategory['category_id']) {
                    ItemCategory::create([
                        'item_id' => $entry['item_id'],
                        'category_id' => $newCategory['category_id']
                    ]);
                }
            }
        }
    }

    public function copyModifiers($from,$to){
        /*copy modifier groups*/
        $modifierGroup = ModifierGroup::where('restaurant_id', '=', $from)->get();
        foreach ($modifierGroup as $val) {
            $entry = new ModifierGroup();
            $entry->restaurant_id = $to;
            $entry->modifier_group_name = $val['modifier_group_name'];
            $entry->item_exactly = $val['item_exactly'];
            $entry->item_range_from = $val['item_range_from'];
            $entry->item_range_to = $val['item_range_to'];
            $entry->item_maximum = $val['item_maximum'];
            $entry->single_item_maximum = $val['single_item_maximum'];
            $entry->modifier_group_identifier = $val['modifier_group_identifier'];
            $entry->save();

            $modifierItems = ModifierItems::where('modifier_group_id', '=', $val['modifier_group_id'])->get();
            foreach ($modifierItems as $v) {

                $item = Item::find($v['item_id']);
                $newItem = Item::where('item_name', '=', $item['item_name'])
                    ->where('item_price', '=', $item['item_price'])
                    ->where('restaurant_id', '=', $to)
                    ->first();

                if ($newItem['item_id']) {
                    $sub_entry = new ModifierItems();
                    $sub_entry->modifier_group_id = $entry['modifier_group_id'];
                    $sub_entry->item_id = $newItem['item_id'];
                    $sub_entry->added_from = $v['added_from'];
                    $sub_entry->order_no = $v['order_no'];
                    $sub_entry->item_name = $v['item_name'];
                    $sub_entry->item_price = $v['item_price'];
                    $sub_entry->item_image = $v['item_image'];
                    $sub_entry->item_description = $v['item_description'];
                    $sub_entry->its_own = $v['its_own'];
                    $sub_entry->is_in_stock = $v['is_in_stock'];
                    $sub_entry->save();

                    $modifierRelation = new ModifierGroupRelations();
                    $modifierRelation->modifier_group_id = $entry['modifier_group_id'];
                    $modifierRelation->item_id = $newItem['item_id'];
                    $modifierRelation->save();
                }
            }
        }
    }
}
