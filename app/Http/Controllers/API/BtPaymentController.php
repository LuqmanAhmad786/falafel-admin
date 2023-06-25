<?php

namespace App\Http\Controllers\API;

use App\Models\Order\Order;
use App\Models\OrderPayment;
use App\Models\SavedCard;
use App\Models\User\BillingAddress;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;
use Braintree\PaymentMethod;

class BtPaymentController extends Controller
{
    public function createCustomerPayMethod(Request $request){
        if(!$request->input('billing_address_id')){
            $validator = Validator::make($request->all(), [
                'address_line_1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'postcode' => 'required',
                'phone' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
            }
        }

        $validator = Validator::make($request->all(), [
            'nonce' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        $user = User::where('id',Auth::user()->id)->first();

        if($user->bt_customer_id){
            try {
                $response = PaymentMethod::create([
                    'customerId' => $user->bt_customer_id,
                    'paymentMethodNonce' => $request->input('nonce'),
                    'options' => [
                        'verifyCard' => true
                    ]
                ]);
                if($response->success){
                    if($response->paymentMethod->verification->cvvResponseCode === 'M' && ($response->paymentMethod->verification->avsPostalCodeResponseCode === 'M' || $response->paymentMethod->verification->avsPostalCodeResponseCode === 'U')){
                        if($request->input('billing_address_id')){
                            $billing_address_id = $request->input('billing_address_id');
                        }else{
                            $newBillingAddress = new BillingAddress();
                            $newBillingAddress->user_id = Auth::user()->id;
                            $newBillingAddress->address_line_1 = $request->input('address_line_1');
                            $newBillingAddress->address_line_2 = $request->input('address_line_2');
                            $newBillingAddress->city = $request->input('city');
                            $newBillingAddress->state = $request->input('state');
                            $newBillingAddress->postcode = $request->input('postcode');
                            $newBillingAddress->phone = $request->input('phone');
                            $newBillingAddress->save();

                            $billing_address_id = $newBillingAddress->id;
                        }
                        $this->saveCardDb($response->paymentMethod, $request->input('is_default'),$billing_address_id,$request->input('nickname'));
                        return response()->json(apiResponseHandler([], 'Success'), 200);
                    }else{
                        return response(apiResponseHandler([], 'Card declined. Invalid verification details.', 400), 400);
                    }
                }elseif($response->errors){
                    return response(apiResponseHandler([], 'Card declined. Please try with different card.', 400), 400);
                }
            }
            catch (Exception $e){
                return response(apiResponseHandler([], $e->getMessage(), 400), 400);
            }
        }
        else{
            try{
                $result = \Braintree\Customer::create([
                    'firstName' => Auth::user()->first_name,
                    'lastName' => Auth::user()->last_name,
                    'phone' => Auth::user()->mobile,
                    'email' => Auth::user()->email
                ]);
                if ($result->success) {
                    User::where('id',Auth::user()->id)->update([
                        'bt_customer_id' => $result->customer->id
                    ]);

                    try {
                        $response = PaymentMethod::create([
                            'customerId' => $result->customer->id,
                            'paymentMethodNonce' => $request->input('nonce'),
                            'options' => [
                                'verifyCard' => true
                            ]
                        ]);

                        if($response->success){
                            if($response->paymentMethod->verification->cvvResponseCode === 'M' && ($response->paymentMethod->verification->avsPostalCodeResponseCode === 'M' || $response->paymentMethod->verification->avsPostalCodeResponseCode === 'U')){
                                $billing_address_id = '';
                                if($request->input('billing_address_id')){
                                    $billing_address_id = $request->input('billing_address_id');
                                }else{
                                    $newBillingAddress = new BillingAddress();
                                    $newBillingAddress->user_id = Auth::user()->id;
                                    $newBillingAddress->address_line_1 = $request->input('address_line_1');
                                    $newBillingAddress->address_line_2 = $request->input('address_line_2');
                                    $newBillingAddress->city = $request->input('city');
                                    $newBillingAddress->state = $request->input('state');
                                    $newBillingAddress->postcode = $request->input('postcode');
                                    $newBillingAddress->phone = $request->input('phone');
                                    $newBillingAddress->save();

                                    $billing_address_id = $newBillingAddress->id;
                                }
                                $this->saveCardDb($response->paymentMethod, $request->input('is_default'),$billing_address_id,$request->input('nickname'));
                                return response()->json(apiResponseHandler([], 'Success'), 200);
                            }else{
                                return response(apiResponseHandler([], 'Card declined. Invalid verification details.'.$response->paymentMethod->verification->avsPostalCodeResponseCode, 400), 400);
                            }
                        }elseif($response->errors){
                            foreach($result->errors->deepAll() AS $error) {
                                Log::debug($error->code . ": " . $error->message . "\n");
                            }
                            return response(apiResponseHandler([], 'Card declined. Please try with different card.', 400), 400);
                        }
                    }
                    catch (Exception $e){
                        return response(apiResponseHandler([], $e->getMessage(), 400), 400);
                    }
                    return response()->json(apiResponseHandler([], 'Success'));
                } else {
                    foreach($result->errors->deepAll() AS $error) {
                        echo($error->code . ": " . $error->message . "\n");
                    }
                }
            } catch (Exception $e){
                return response(apiResponseHandler([], $e->getMessage(), 400), 400);
            }
        }
    }

    public function saveCardDb($data, $isDefault, $billingAddressId, $nickname){
        $saveCard = new SavedCard();
        $saveCard->user_id = Auth::user()->id;
        $saveCard->token = $data->globalId;
        $saveCard->name = $data->cardholderName;
        $saveCard->expiry_month = $data->expirationMonth;
        $saveCard->expiry_year = $data->expirationYear;
        $saveCard->card_type = $data->cardType;
        $saveCard->masked_card_number = 'XXXX '.$data->last4;
        $saveCard->is_default = $isDefault ? $isDefault : 0;
        $saveCard->nickname = $nickname;
        $saveCard->billing_address_id = $billingAddressId;
        $saveCard->save();

        return true;
    }

    public function saveCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'expiry_month' => 'required|digits_between:1,12',
            'expiry_year' => 'required',
            'card_number' => 'required|digits_between:13,19',
            'cvc' => 'required|digits_between:3,4'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], 'Looks like you missed something', 400), 400);
        }

        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        $user = User::where('id',Auth::user()->id)->first();
        if($user->customer_id){

            try {
                $result = \Braintree\CreditCard::create([
                    'cardholderName' => $user->first_name.' '.$user->last_name,
                    'customerId' => $user->customer_id,
                    'number' => $request->input('card_number'),
                    'expirationDate' => $request->input('expiry_month')."/".$request->input('expiry_year'),
                    'cvv' => $request->input('cvc')
                ]);

                if ($result->success) {
                    $cardGroupId = time();

                    $saveCard = new SavedCard();
                    $saveCard->user_id = Auth::user()->id;
                    $saveCard->token = $result->creditCard->globalId;
                    $saveCard->name = $result->creditCard->cardholderName;
                    $saveCard->expiry_month = $result->creditCard->expirationMonth;
                    $saveCard->expiry_year = $result->creditCard->expirationYear;
                    $saveCard->card_type = $result->creditCard->cardType;
                    $saveCard->masked_card_number = $result->creditCard->maskedNumber;
                    $saveCard->is_default = $request->input('is_default') ? $request->input('is_default') : 0;
                    $saveCard->restaurant_id = 1;
                    $saveCard->card_group_id = $cardGroupId;
                    $saveCard->save();

                    return response()->json(apiResponseHandler([$saveCard], 'Success'));
                } else {
                    foreach($result->errors->deepAll() AS $error) {
                        echo($error->code . ": " . $error->message . "\n");
                    }
                }
            } catch (Exception $e){
                return response(apiResponseHandler([], $e->getMessage(), 400), 400);
            }

        } else{
            return response()->json(apiResponseHandler([], 'Customer not registered with BT.', 400), 400);
        }
    }

    public function deleteCard(Request $request){
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')->where('user_id', Auth::user()->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        if(Auth::user()->id){
            $card = SavedCard::where('id', '=', $request->input('card_id'))->first();

            SavedCard::where('id', '=', $request->input('card_id'))->delete();

            try {
                $result = \Braintree\CreditCard::delete($card->token);
                if ($result->success) {
                    return response()->json(apiResponseHandler([], 'Success'));
                } else{
                    return response()->json(apiResponseHandler([], 'Failed', 400), 400);
                }
            } catch (Exception $e){
                return response(apiResponseHandler([], $e->getMessage(), 400), 400);
            }
        } else{
            return response()->json(apiResponseHandler([], 'Failed', 400), 400);
        }
    }

    public function makeOrderPayment($cardId, $order){
        $card = SavedCard::where('id', '=', $cardId)->first();

        if($card){
            \Braintree\Configuration::environment(env('BT_ENV'));
            \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
            \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
            \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

            $merchantAccountId = 'qualwebs';
            try {
                $result = \Braintree\PaymentMethodNonce::create($card->token);
                $nonce = $result->paymentMethodNonce->nonce;
                $total = $order->total_amount;

                try{
                    $result = \Braintree\Transaction::sale([
                        'amount' => (float)($total),
                        'paymentMethodNonce' => $nonce,
                        'options' => [
                            'submitForSettlement' => True
                        ],
//                        'merchantAccountId' => $merchantAccountId
                    ]);

                    if ($result->success) {
                        $orderPayment = new OrderPayment();
                        $orderPayment->order_id = $order->order_id;
                        $orderPayment->order_code = $result->transaction->id;
                        $orderPayment->token = $result->transaction->creditCard['token'];
                        $orderPayment->order_description = '';
                        $orderPayment->amount = $result->transaction->amount;
                        $orderPayment->currency_code = 'USD';
                        $orderPayment->payment_status = $result->transaction->status;
                        $orderPayment->expiry_month = $result->transaction->creditCard['expirationMonth'];
                        $orderPayment->expiry_year = $result->transaction->creditCard['expirationYear'];
                        $orderPayment->card_type = $result->transaction->creditCard['cardType'];
                        $orderPayment->masked_card_number = $result->transaction->creditCard['last4'];
                        $orderPayment->save();
                        return true;
                    } else {
                        return false;
                    }
                } catch (Exception $e) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } else{
            return false;
        }
    }

    public function guestMakeOrderPayment(Request $request){
        $validator = Validator::make($request->all(), [
            'paymentmethodnonce' => 'required',
            'orderid' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        $total = 5;
        try{
            $result = \Braintree\Transaction::sale([
                'amount' => (float)($total),
                'paymentMethodNonce' => $request->input('paymentmethodnonce'),
                'options' => [
                    'submitForSettlement' => True
                ]
            ]);

            if ($result->success) {
                $order = Order::find($request->input('orderid'));
                $orderPayment = new OrderPayment();
                $orderPayment->order_id = $order->order_id;
                $orderPayment->order_code = $result->transaction->id;
                $orderPayment->token = isset($result->transaction->globalId) ? $result->transaction->globalId : '';
                $orderPayment->order_description = '';
                $orderPayment->amount = $result->transaction->amount;
                $orderPayment->currency_code = 'USD';
                $orderPayment->payment_status = $result->transaction->status;
                $orderPayment->expiry_month = $result->transaction->creditCard['expirationMonth'];
                $orderPayment->expiry_year = $result->transaction->creditCard['expirationYear'];
                $orderPayment->card_type = $result->transaction->creditCard['cardType'];
                $orderPayment->masked_card_number = $result->transaction->creditCard['last4'];
                $orderPayment->reference_number = $result->transaction->id;
                $orderPayment->ticket_number = $result->transaction->id;
                $orderPayment->save();
                return $orderPayment;
            } else {
                return response()->json(apiResponseHandler([], 'Something went wrong.', 400), 400);
            }
        } catch (Exception $e) {
            return response(apiResponseHandler([], $e->getMessage(), 400), 400);
        }
    }

    public function walletTransaction($order, $data){
        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

        $merchantAccountId = 'qualwebs';

        try{
            $result = \Braintree\Transaction::sale([
                'amount' => (float)($order->total_amount),
                'paymentMethodNonce' => $data['token'],
                'options' => [
                    'submitForSettlement' => True
                ],
                'merchantAccountId' => $merchantAccountId
            ]);

            if ($result->success) {
                $orderPayment = new OrderPayment();
                $orderPayment->order_id = $order->order_id;
                $orderPayment->order_code = $result->transaction->id;
                $orderPayment->token = $result->transaction->id;
                $orderPayment->order_description = '';
                $orderPayment->amount = $result->transaction->amount;
                $orderPayment->currency_code = 'USD';
                $orderPayment->payment_status = $result->transaction->status;
                $orderPayment->expiry_month = '';
                $orderPayment->expiry_year = '';
                $orderPayment->card_type = '';
                $orderPayment->masked_card_number = '';
                $orderPayment->save();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function makeRefund($trxId, $amount){
        \Braintree\Configuration::environment(env('BT_ENV'));
        \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
        \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
        \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

//        try {
//            $trx = \Braintree\Transaction::find($trxId);
//            if($trx->status == 'submitted_for_settlement'){
//                $trxVoid = \Braintree\Transaction::void($trxId);
//                dd($trxVoid);
//            }
//        }catch (Exception $e){
//            return false;
//        }
//        exit;
        try{
            $refund = \Braintree\Transaction::refund($trxId, (float) $amount);
            if($refund->success){
                return $refund->transaction;
            } else{
                Log::debug($refund->error);
                return false;
            }
        } catch (Exception $e){
            Log::debug($e);
            return false;
        }
    }

    public function makeGiftCardPayment($cardId, $amount){
        $card = SavedCard::where('id', '=', $cardId)->first();

        if($card){
            \Braintree\Configuration::environment(env('BT_ENV'));
            \Braintree\Configuration::merchantId(env('BT_MERCHANTID'));
            \Braintree\Configuration::publicKey(env('BT_PUBLICKEY'));
            \Braintree\Configuration::privateKey(env('BT_PRIVATEKEY'));

            $merchantAccountId = 'qualwebs';
            try {
                $result = \Braintree\PaymentMethodNonce::create($card->token);
                $nonce = $result->paymentMethodNonce->nonce;
                $total = $amount;

                try{
                    $result = \Braintree\Transaction::sale([
                        'amount' => (float)($total),
                        'paymentMethodNonce' => $nonce,
                        'options' => [
                            'submitForSettlement' => True
                        ],
//                        'merchantAccountId' => $merchantAccountId
                    ]);

                    if ($result->success) {
                        return true;
                    } else {
                        return false;
                    }
                } catch (Exception $e) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } else{
            return false;
        }
    }
}
