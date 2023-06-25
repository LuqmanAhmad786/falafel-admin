<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\CloverItem;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\ModifierGroup;
use App\Models\ModifierGroupRelations;
use App\Models\ModifierItems;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\OrderDetail;
use App\Models\Restaurant\CloverToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CloverController extends Controller
{
    public $mid;
    private $authToken;

    public function __construct($mid)
    {
        $this->mid = $mid;
        $this->authToken = $this->getAuthToken();
    }

    public function getAuthToken(){
        $token = CloverToken::where('merchant_id', $this->mid)->first();
        return Crypt::decryptString($token->auth_token);
    }

    public function fetchData($type)
    {
        if ($type == 'categories') {
            $data = $this->getAttributeByType($this->mid, 'categories');
            foreach ($data->elements as $item) {
                if(str_contains($item->name, '*')){
                    $catId = Category::updateOrCreate(['clover_id' => $item->id], [
                        'category_name' => str_replace('*','',$item->name),
                        'order_no' => $item->sortOrder,
                        'restaurant_id' => Session::get('my_restaurant')
                    ]);

                    $menu = Menu::where('restaurant_id',Session::get('my_restaurant'))->first();
                    if($menu){
                        MenuCategory::updateOrCreate(['category_id' => $catId->category_id], ['menu_id' => $menu->menu_id]);
                    }
                }
            }
        }

        if ($type == 'items') {
            $data = $this->getAttributeByType($this->mid, 'items?limit=100');
            foreach ($data->elements as $item) {
                if($item->hidden){
                    $data = [
                        'item_name' => str_replace('*','',$item->name),
                        'item_price' => $item->price / 100,
                        'restaurant_id' => Session::get('my_restaurant'),
                        'item_description' => str_replace('*','',$item->name),
                        'clover_stock' => isset($item->stockCount) ? $item->stockCount : 0,
                        'menu_type' => 1,
                        'is_common' => 1,
                        'is_in_stock' => 1,
                        'its_own' => 2,
                        'tax_applicable' => 1
                    ];

                    $exists = Item::where('clover_id', $item->id)->count();

                    if ($exists > 0) {
                        unset($data['item_name']);
                    }

                    Item::updateOrCreate(['clover_id' => $item->id], $data);

                    $category = $this->getItemCategory($this->mid, $item->id);

                    if (count($category->elements) > 0) {
                        foreach ($category->elements as $itemCat) {
                            $catId = Category::where('clover_id', $itemCat->id)->first();
                            $newItemId = Item::where('clover_id', $item->id)->first();

                            // DELETE OLD ITEM CATEGORY RELATION
                            if($catId && $newItemId){
                                ItemCategory::where('item_id', $newItemId->item_id)->where('category_id', $catId->category_id)->delete();
                                ItemCategory::create([
                                    'item_id' => $newItemId->item_id,
                                    'category_id' => $catId->category_id
                                ]);
                            }
                        }
                    }
                }
            }
        }

        if ($type == 'modifier') {
            $data = $this->getAttributeByType($this->mid, 'modifier_groups?expand=items&limit=100');
            //dd($data);
            if (count($data->elements) > 0) {
                foreach ($data->elements as $modifier) {
                    if(str_contains($modifier->name, '*')){
                        $mgId = ModifierGroup::updateOrCreate(['clover_id' => $modifier->id], [
                            'modifier_group_name' => str_replace('*','',$modifier->name),
                            'item_exactly' => 1,
                            'single_item_maximum' => 1,
                            'restaurant_id' => Session::get('my_restaurant'),
                            'modifier_group_identifier' => str_replace('*','',$modifier->name)
                        ]);

                        $allMItems = explode(',', $modifier->modifierIds);
                        foreach ($allMItems as $item) {
                            $itemData = $this->getModifierItem($modifier->id, $item);
                            $mItemId = Item::updateOrCreate(['clover_id' => $itemData->id], [
                                'item_name' => str_replace('*','',$itemData->name),
                                'item_price' => $itemData->price / 100,
                                'restaurant_id' => Session::get('my_restaurant'),
                                'item_description' => str_replace('*','',$itemData->name),
                                'menu_type' => 2,
                                'is_common' => 0,
                                'is_in_stock' => 1,
                                'its_own' => 1,
                                'tax_applicable' => 1
                            ]);
                            $itemId = ModifierItems::updateOrCreate(['clover_id' => $itemData->id], [
                                'modifier_group_id' => $mgId->modifier_group_id,
                                'item_id' => $mItemId->item_id,
                                'added_from' => 2,
                                'item_name' => str_replace('*','',$itemData->name),
                                'item_price' => $itemData->price / 100
                            ]);
                        }

                        $allMainItems = $modifier->items->elements;
                        foreach ($allMainItems as $mainItem) {
                            $actualItem = Item::where('clover_id', $mainItem->id)->first();
                            ModifierItems::updateOrCreate(['modifier_group_id' => $mgId->modifier_group_id, 'item_id' => $actualItem->item_id], [
                                'modifier_group_id' => $mgId->modifier_group_id,
                                'item_id' => $actualItem->item_id,
                                'added_from' => 1
                            ]);
                            ModifierGroupRelations::updateOrCreate(['modifier_group_id' => $mgId->modifier_group_id, 'item_id' => $actualItem->item_id], ['modifier_group_id' => $mgId->modifier_group_id, 'item_id' => $actualItem->item_id]);
                        }
                    }
                }
            }
        }
        return true;
    }

    public function cloverItemsMapping(Request $request)
    {
        $synced_clover_items = CloverItem::count();

        $categories = Category::select('*')->orderBy('created_at', 'desc')->get();

        $clover_items = CloverItem::select('*')->orderBy('created_at', 'desc')->get();

        $clover_html = '';

        $clover_html .= '<select class="form-control" style="max-width:30%;">';

// generate the options for the select
        $clover_html .= '<option value="">Select</option>';
        foreach ($clover_items as $status) {
            $clover_html .= '<option value="' . $status->item_id . '">' . $status->item_name . ' - ' . $status->item_price . '</option>';
        }
// close the select input
        $clover_html .= '</select>';


        return view('dashboard.clover_items_mapping', ['clover_items_count' => $synced_clover_items, 'categories' => $categories, 'clover_html' => $clover_html]);
    }

    public function pushData($type)
    {
        if ($type == 'items') {
            $items = Item::get();
            foreach ($items as $item) {
                if ($item->clover_id) {
                    $data = ['name' => $item->item_name, 'price' => $item->item_price * 100];
                    $itemData = json_encode($data);
                    $this->updateItemClover('V04670aFKJQXP1', $item->clover_id, $itemData);
                } else {
                    $data = ['name' => $item->item_name, 'price' => $item->item_price];
                    $itemData = json_encode($data);
                    $cloverItem = $this->createItemClover($this->mid, $itemData);
                    Item::where('item_id', $item->item_id)->update(['clover_id' => $cloverItem->id]);
                }
            }
        }
        return Redirect::back();
    }

    public function getAttributeByType($mid, $type = 'items')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/$mid/$type",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\r\n  \"state\": \"open\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function getItemCategory($mid, $itemId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/$mid/items/$itemId/categories",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\r\n  \"state\": \"open\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function createItemClover($mid, $postData)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/$mid/items",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function updateItemClover($mid, $itemId, $postData)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/$mid/items/$itemId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function createOrder($order, $restaurant)
    {
        $note = 'Order Number: ' . $order['order_id']
            . '            Pickup Date: ' . date('n/j/Y', strtotime($order['pickup_date']))
            . '            Pickup Time: ' . date('g:i a', $order['pickup_time'])
            . '            Customer Phone: ' . $order->user_number
            . '          Customer Name: ' . $order->user_first_name . ' ' . $order->user_last_name;
        $data = json_encode([
            'total' => $order->total_amount * 100,
            'externalReferenceId' => $order->order_id,
            'employee' => ['id' => $restaurant->clover_employee_id],
            'paymentState' => "PAID",
            'note' => $note
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/orders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $cloverOrderData = json_decode($response, true);

        // SET ORDER ID
        Order::where('order_id',$order['order_id'])->update(['clover_id'=>$cloverOrderData['id']]);

        // SET ORDER PAYMENT TO CLOVER
        $this->makeOrderPayment($order, $restaurant, $cloverOrderData['id']);
        // LINE ITEMS TO CLOVER
        $this->createLineItems($cloverOrderData['id'], $order);

        // SEND PRINT EVENT TO CLOVER POS
        $this->printCloverOrder($cloverOrderData['id']);
    }

    public function makeOrderPayment($order, $restaurant, $cloverOrderId){

        $postFields = json_encode([
            "tender" => ['id' => $restaurant->clover_tender_id],
            "amount" => $order->total_amount * 100
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/orders/{$cloverOrderId}/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

    }

    public function createLineItems($cloverId, $order)
    {
        $items = OrderDetail::where('order_id',$order->order_id)->get();
        if(count($items)){
            foreach ($items as $item) {
                $itemId = Item::where('item_id', '=', $item['item_id'])->first();
                if ($itemId['clover_id']) {
                    $data = json_encode([
                        "item" =>
                            [
                                'id' => $itemId['clover_id']
                            ]
                    ]);
                    if($item['item_count']){
                        for($i=1;$i<=$item['item_count'];$i++){
                            $lineItemData = $this->pushLineItemClover($cloverId, $data);

                            // GET MODIFIER AND ITEMS
                            $modifiers = OrderItem::where('order_detail_id',$item->order_detail_id)->get();

                            if(count($modifiers)){
                                foreach ($modifiers AS $modifier){
                                    $mgId = $modifier->modifier_group_id;
                                    $mItemId = $modifier->item_id;

                                    $modifierGroupCloverInfo = ModifierGroup::where('modifier_group_id', $mgId)->first();
                                    $modifierItemCloverInfo = ModifierItems::where('id', $mItemId)->first();

                                    if($modifierGroupCloverInfo->clover_id && $modifierItemCloverInfo->clover_id){
                                        $this->pushLineItemModifiers($cloverId,$lineItemData['id'],$modifierGroupCloverInfo->clover_id,$modifierItemCloverInfo->clover_id);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function pushLineItemClover($cloverId, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/orders/$cloverId/line_items",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function pushLineItemModifiers($orderId,$lineItemId, $modifierGroupId, $modifierItemId){
        $postFields = json_encode([
            "modifier" => [
                "id" => $modifierItemId,
                "modifierGroup" => [
                    "id" => $modifierGroupId
                ]
            ]
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/orders/{$orderId}/line_items/{$lineItemId}/modifications",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

    public function getModifierItem($modifierGroupId, $modifierItemId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/modifier_groups/$modifierGroupId/modifiers/$modifierItemId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->authToken}"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function sendNotificationToCloverApp($orderId){
        $postFields = json_encode([
           "event" => "printOrder",
            "data" => $orderId
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clover.com/v3/apps/B00HTEGWX3000/merchants/{$this->mid}/notifications",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }

    public function printCloverOrder($orderId){

        $postFields = json_encode([
            "orderRef" => [
                "id" => $orderId
            ]
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clover.com/v3/merchants/{$this->mid}/print_event",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return json_decode($response);
    }
}
