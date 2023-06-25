<?php

use App\GlobalSettings;
use App\Models\AdminReward;
use App\Models\Cart\CartDetail;
use App\Models\Cart\CartItem;
use App\Models\Cart\CartList;
use App\Models\Item;
use App\Models\ModifierItems;
use App\Models\RewardCoupon;
use App\Models\RewardsItem;
use Illuminate\Support\Facades\Log;
use App\Models\Bonus;
use App\Models\Restaurant;
use App\Models\User\CardTransactionHistory;
use Illuminate\Support\Facades\Auth;

function apiResponseHandler($response = [], $message = '', $status = 200, $limit = '')
{
    return [
        'response' => $response,
        'message' => $message,
        'status' => $status,
        'pagination_limit' => $limit,
    ];
}

function sendFirebaseNotification($token, $notification, $platform)
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
        'Authorization: key=' . env('FIREBASE_SERVER_KEY'),
        'Content-Type: application/json'
    );

    $body = [];

    if ($platform == 1) {
        $body = [
            'registration_ids' => [$token],
            'data' => $notification,
        ];
    } else if ($platform == 2) {
        $body = [
            'registration_ids' => [$token],
            "content_available" => true,
            "mutable_content" => true,
            'notification' => $notification
        ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    $result = curl_exec($ch);
    $status = json_decode($result, true);

    if ($status['failure'] === FALSE) {
        curl_close($ch);
        return FALSE;
    } else {
        curl_close($ch);
        return TRUE;
    }
}

function generateRandomString($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function generateRefId($length = 4)
{
    $characters = '0123456789';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function generateOtp($length = 5)
{
    $characters = '0123456789';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function sendEmail($template, $to_email, $subject,$replyTo = 'noreply@falafelcorner.us')
{
    return true;
    $data = array(
        'email' => $to_email,
        'clientid' => 'Falafel Corner',
        'key' => 'wHfWHwpDYFf3rEpbbc1tipQu28jqoOf0qeLNunkrqtwU1J1lPPKepJvJX7ILDpSy',
        'subject' => $subject,
        'message' => $template,
        'file' => '',
        'replyto' => $replyTo
    );
    $url = 'https://esmtp.qualwebs.com/qw/api/api.php';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($handle);

    curl_close($handle);
}

function sendEmailFalafel($template, $to_email, $subject,$replyTo = 'noreply@falafelcorner.us')
{
    $data = array(
        'email' => $to_email,
        'clientid' => 'Falafel Corner',
        'key' => 'wHfWHwpDYFf3rEpbbc1tipQu28jqoOf0qeLNunkrqtwU1J1lPPKepJvJX7ILDpSy',
        'subject' => $subject,
        'message' => $template,
        'file' => '',
        'replyto' => $replyTo
    );
    $url = 'https://esmtp.qualwebs.com/qw/api/api.php';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($handle);

    curl_close($handle);
}

function cartCalculation($cartListId, $user_id = null)
{
    $cart = CartList::where('cart_list_id', '=', $cartListId)->first();
    $cartItems = CartItem::where('cart_list_id', '=', $cartListId)->get();
    $orderTotal = 0;
    $discountAmount = $cart->discount_amount ? $cart->discount_amount : 0;
    $discountPercent = 0;
    $orderTotalTaxable = 0;

    if ($cart->coupon_id != null && !$cart->bonus_id) {
        $couponInfo = RewardCoupon::where('coupon_id', '=', $cart->coupon_id)->first();
        if ($couponInfo->coupon_type == 3) {
            $rewardItems = AdminReward::where('admin_reward_id', '=', $couponInfo->admin_reward_id)->first();

            $isExistInCart = (new CartItem())
                ->where('cart_items.item_id', $rewardItems->item_id)
                ->leftJoin('items', 'items.item_id', '=', 'cart_items.item_id')
                ->get();

            if (count($isExistInCart)) {
                $discountAmount = $isExistInCart[0]['item_price'];
            }
        } else {
            $rewardItems = RewardsItem::where('flag', '=', $couponInfo->coupon_type)->pluck('item_id');

            $isExistInCart = CartItem::where('cart_list_id', '=', $cartListId)
                ->leftJoin('items', 'items.item_id', '=', 'cart_items.item_id')
                ->whereIn('cart_items.item_id', $rewardItems)->orderBy('items.item_price', 'ASC')->first();
            if ($isExistInCart) {
                $discountAmount = $isExistInCart['item_price'];
            }else{
                $discountAmount = 0;
            }
        }
    }else{
        $discountAmount = 0;
    }

    if ($cart->bonus_id != null && !$cart->coupon_id) {
        $bonusInfo = Bonus::where('bonus_id', '=', $cart->bonus_id)->first();
        if($bonusInfo->bonus_type == 3){
            $isExistInCart = (new CartItem())
                ->where('cart_items.item_id', $bonusInfo->bonus_free_item_id)
                ->leftJoin('items', 'items.item_id', '=', 'cart_items.item_id')
                ->get();

            if (count($isExistInCart)) {
                $discountAmount = $isExistInCart[0]['item_price'];
            }else{
                $discountAmount = 0;
            }
        }

        if($bonusInfo->bonus_type == 4){
            $discountPercent = $bonusInfo->bonus_discount;
        }
    }

    foreach ($cartItems as $item) {
        $mainItem = Item::where('item_id', '=', $item['item_id'])->first();
        if(!$mainItem['tax_applicable']){
            $orderTotal += $mainItem['item_price'] * $item['item_count'];
        }
        else{
            $orderTotalTaxable += $mainItem['item_price'] * $item['item_count'];
        }
        $cartItemDetails = CartDetail::where('cart_item_id', '=', $item['cart_item_id'])->get();

        if (count($cartItemDetails)) {
            foreach ($cartItemDetails as $subItem) {
                $singleItem = ModifierItems::where('id', '=', $subItem['item_id'])->first();
                if(!$mainItem['tax_applicable']){
                    $orderTotal += $item['item_count'] * $singleItem['item_price'];
                }
                else{
                    $orderTotalTaxable += $item['item_count'] * $singleItem['item_price'];
                }
            }
        }
    }

    if($discountPercent > 0){
        $discountAmount = (($orderTotal*$discountPercent)/100) + (($orderTotalTaxable*$discountPercent)/100);
    }

    $finalTotal = $orderTotal - $discountAmount;

    $restaurant = Restaurant::find($cart->restaurant_id);
    $taxVal = 8.25;
    if($restaurant->tax_rate){
        $taxVal = $restaurant->tax_rate;
    }
    $taxTotal = round($orderTotalTaxable * ($taxVal  / 100),2);
    CartList::where('cart_list_id', '=', $cartListId)->update([
        'total_tax' => $taxTotal,
        'order_total' => $orderTotal + $orderTotalTaxable,
        'total_amount' => $finalTotal + $orderTotalTaxable + $taxTotal + $cart->delivery_fee,
        'discount_amount' => $discountAmount
    ]);

    return CartList::where('cart_lists.cart_list_id', '=', $cartListId)->first();
}

function fromCamelCase($input)
{
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

function getPaginationLimit()
{
    return \App\Models\PaginationLimit::first()->limit;
}


function generateCustomerId($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function phoneFormat($number){
    $number = '+1'.$number;
    if(  preg_match( '/^\+\d(\d{3})(\d{3})(\d{4})$/', $number,  $matches ) )
    {
        $result = '('.$matches[1] . ') ' .$matches[2] . '-' . $matches[3];
        return $result;
    }
}

function createCardTrxHistory($data){
    $data['user_id'] = Auth::user()->id;
    CardTransactionHistory::create($data);
    return true;
}

function createCardTrxHistoryServer($data, $userId){
    $data['user_id'] = $userId;
    CardTransactionHistory::create($data);
    return true;
}

function sendCloudNotification($title, $body, $tokens, $data){
    $rtController = new \App\Http\Controllers\RealTimeController();

    $rtController->cloudNotifications($title, $body, $tokens, $data);

    return true;
}

function twillioSms($message, $number){
    $postFields = [
      'Body' => $message,
      'To' => $number,
      'From' => '+18887780717'
    ];

    $postFields = http_build_query($postFields);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/AC06fe336a31befb70a74b0cfda4c2d215/Messages.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic QUMwNmZlMzM2YTMxYmVmYjcwYTc0YjBjZmRhNGMyZDIxNTo3NWFlYzNkZjZlZmU3YTdjOTliNzY0MjE1Zjc4ODIxOA==",
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

function twillioCall($message, $number){
    $postFields = [
        'Twiml' => '<Response><Pause length="1"/><Say> You have received a new order '.$message.' from Falafel Corner Website.</Say> <Pause length="1"/><Say>Thank You.</Say></Response>',
        'To' => $number,
        'From' => '+18887780717'
    ];

    $postFields = http_build_query($postFields);
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/AC06fe336a31befb70a74b0cfda4c2d215/Calls.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic QUMwNmZlMzM2YTMxYmVmYjcwYTc0YjBjZmRhNGMyZDIxNTo3NWFlYzNkZjZlZmU3YTdjOTliNzY0MjE1Zjc4ODIxOA==",
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

function removeSpecialChar($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '', $string); // Replaces multiple hyphens with single one.
}
