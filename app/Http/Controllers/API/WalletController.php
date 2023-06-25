<?php

namespace App\Http\Controllers\API;

use App\Models\OrderPayment;
use App\Models\User\UserCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;

class WalletController extends Controller
{
    public function validateCardBalance($cardId, $amount){
        $card = UserCard::find($cardId);

        if($card->balance < $amount){
            return false;
        }

        return true;
    }
    public function makeOrderPayment($cardId,$amount,$orderId){
        $card = UserCard::find($cardId);

        if($card->balance < $amount){
            return false;
        }

        $card->balance = $card->balance - $amount;
        $card->save();

        $orderPayment = new OrderPayment();
        $orderPayment->order_id = $orderId;
        $orderPayment->order_code = Uuid::uuid4();
        $orderPayment->token = $card->unique_id;
        $orderPayment->order_description = '';
        $orderPayment->amount = $amount;
        $orderPayment->currency_code = 'USD';
        $orderPayment->payment_status = 'Success';
        $orderPayment->save();

        return true;
    }
}
