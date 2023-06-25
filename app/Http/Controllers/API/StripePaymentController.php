<?php

namespace App\Http\Controllers\API;

use App\Models\Order\Order;
use App\Models\OrderPayment;
use App\Models\Restaurant;
use App\Models\SavedCard;
use App\Models\User\BillingAddress;
use App\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Token;

class StripePaymentController extends Controller
{

    public function createCustomerPayMethod(Request $request)
    {
        if (!$request->input('billing_address_id')) {
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

        $user = User::where('id',Auth::user()->id)->first();

        $data = $request->all();

        $apiKey = $this->getApiKey();
        Stripe::setApiKey($apiKey['secret_key']);

        try{
            if($user->stripe_customer_id){
                try{
                    Customer::createSource($user->stripe_customer_id, ['source' => $data['nonce']['id']]);

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

                    $this->saveCardDb($data['nonce'], $request->input('is_default'),$billing_address_id,$request->input('nickname'));
                }catch (ApiErrorException $e){
                    return response()->json(apiResponseHandler([], $e->getError()->message, 400), 400);
                }
            }else{
                try{
                    $customer = $this->createCustomer($data);
                    if($customer){
                        User::where('id',Auth::user()->id)->update([
                            'stripe_customer_id' => $customer->id
                        ]);

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

                        $this->saveCardDb($data['nonce'], $request->input('is_default'),$billing_address_id,$request->input('nickname'));
                        return response()->json(apiResponseHandler([], 'Success'), 200);
                    }
                }catch (\Exception $e){
                    return response()->json(apiResponseHandler([], $e->getError()->message, 400), 400);
                }
            }
        }catch (ApiErrorException $e) {
            return response()->json(apiResponseHandler([], $e->getError()->message, 400), 400);
        }
    }

    public function saveCardDb($data, $isDefault, $billingAddressId, $nickname = 'my card'){
        $saveCard = new SavedCard();
        $saveCard->user_id = Auth::user()->id;
        $saveCard->token = $data['card']['id'];
        $saveCard->name = Auth::user()->first_name .' '. Auth::user()->last_name;
        $saveCard->expiry_month = $data['card']['exp_month'];
        $saveCard->expiry_year = $data['card']['exp_year'];
        $saveCard->card_type = $data['card']['brand'];
        $saveCard->masked_card_number = 'XXXX '.$data['card']['last4'];
        $saveCard->is_default = $isDefault ? $isDefault : 0;
        $saveCard->nickname = $nickname;
        $saveCard->billing_address_id = $billingAddressId;
        $saveCard->save();

        return true;
    }

    public function getApiKey()
    {
        $keyArray = Config::get('stripe');
        return $keyArray['sandbox'];
    }

    public function makeOrderPayment($cardId, $order){
        $card = SavedCard::where('id', '=', $cardId)->first();

        if($card){
            try {
                $total = $order->total_amount;
                $resaurant = Restaurant::with('bankAccount')->where('id',$order->restaurant_id)->first();
                if($resaurant && $resaurant->bankAccount) {
                    $applicationfee = $resaurant->commission;
                    if($resaurant->commission_type == 2){
                        $applicationfee = ($order->order_total * $applicationfee)/100;
                    }
                    $currency = 'USD';
                    $apiKey = $this->getApiKey();
                    Stripe::setApiKey($apiKey['secret_key']);
                    $result = Charge::create([
                        'amount' => $total * 100,
                        'currency' => $currency,
                        'source' => $card->token,
                        'customer' => Auth::user()->stripe_customer_id,
                        'destination' => $resaurant->bankAccount->bank_account_id,
                        'application_fee' => $applicationfee * 100
                    ]);

                    if ($result->status === 'succeeded') {
                        $orderPayment = new OrderPayment();
                        $orderPayment->order_id = $order->order_id;
                        $orderPayment->order_code = $result->id;
                        $orderPayment->token = $result->source->id;
                        $orderPayment->order_description = '';
                        $orderPayment->amount = $result->amount/100;
                        $orderPayment->currency_code = 'USD';
                        $orderPayment->payment_status = $result->status;
                        $orderPayment->expiry_month = $result->source->exp_month;
                        $orderPayment->expiry_year = $result->source->exp_year;
                        $orderPayment->card_type = $result->source->brand;
                        $orderPayment->masked_card_number = $result->source->last4;
                        $orderPayment->save();
                        return true;
                    } else {
                        return false;
                    }
                }else{
                    Log::debug('Bank not found');
                }
            } catch (ApiErrorException $e) {
                Log::debug($e);
                return false;
            }
        } else{
            Log::debug('Card not found');
            return false;
        }
    }

    public function testPayment(Request $request){
        $resaurant = Restaurant::with('bankAccount')->first();
        $applicationfee = $resaurant->commission;
        if($resaurant->commission_type == 2){
            $applicationfee = ($request['total_amount'] * $applicationfee)/100;
        }
        $currency = 'USD';
        $apiKey = $this->getApiKey();
        Stripe::setApiKey($apiKey['secret_key']);
        $charge = Charge::create([
            'amount' => $request['total_amount'] * 100,
            'currency' => $currency, //env('CASHIER_CURRENCY')
            'source' => 'card_1KoWRHJClpq6GgBH1aI25HOg',
            'customer' => 'cus_LVXW6g69ev6r9u',
            'destination' => $request['stripe_account_id'],
            'application_fee' => $applicationfee * 100
        ]);
        return response()->json(apiResponseHandler($charge,'',200),200);
    }

    /**
     * @param $generatedToken
     * @return Customer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCustomer($data)
    {
        return Customer::create(array(
            'email' => Auth::user()->email,
            'source' => $data['nonce']['id']
        ));
    }

    public function paymentWithToken($amount,$token,$restaurant_id){
        $resaurant = Restaurant::with('bankAccount')->where('id',$restaurant_id)->first();
        if($resaurant && $resaurant->bankAccount){
            $applicationfee = $resaurant->commission;
            if($resaurant->commission_type == 2){
                $applicationfee = ($amount * $applicationfee)/100;
            }
            $currency = 'USD';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $charge = Charge::create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'source' => $token,
                'destination' => $resaurant->bankAccount->bank_account_id,
                'application_fee' => $applicationfee * 100
            ]);
            return $charge;
        }
    }

    public function walletPaymentWithToken($order, $data){
        $resaurant = Restaurant::with('bankAccount')->where('id',$order->restaurant_id)->first();
        if($resaurant){
            $applicationfee = $resaurant->commission;
            if($resaurant->commission_type == 2){
                $applicationfee = ($order->total_amount * $applicationfee)/100;
            }
            $currency = 'USD';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $charge = Charge::create([
                'amount' => $order->total_amount * 100,
                'currency' => $currency,
                'source' => $data['nonce'],
//                'destination' => $resaurant->bankAccount->bank_account_id,
//                'application_fee' => $applicationfee * 100
            ]);
            if($charge->status == "succeeded"){
                $orderPayment = new OrderPayment();
                $orderPayment->order_id = $order->order_id;
                $orderPayment->order_code = $charge->id;
                $orderPayment->token = $charge->source->id;
                $orderPayment->order_description = '';
                $orderPayment->amount = $order->total_amount;
                $orderPayment->currency_code = 'USD';
                $orderPayment->payment_status = 1;
                $orderPayment->expiry_month = $charge->payment_method_details->card->exp_month;
                $orderPayment->expiry_year = $charge->payment_method_details->card->exp_year;
                $orderPayment->card_type = $charge->payment_method_details->card->network;
                $orderPayment->masked_card_number = $charge->payment_method_details->card->last4;
                $orderPayment->save();
                return true;
            } else{
                return false;
            }
        }
        return false;
    }

    public function paymentWithCardId($amount,$data,$restaurant_id){
        $resaurant = Restaurant::with('bankAccount')->where('id',$restaurant_id)->first();
        if($resaurant && $resaurant->bankAccount){
            $applicationfee = $resaurant->commission;
            if($resaurant->commission_type == 2){
                $applicationfee = ($amount * $applicationfee)/100;
            }
            $currency = 'USD';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $charge = Charge::create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'source' => $data['card_id'],
                'customer' => $data['customer_id'],
                'destination' => $resaurant->bankAccount->bank_account_id,
                'application_fee' => $applicationfee * 100
            ]);
            return $charge;
        }
    }

    public function walletPaymentWithCardId($order, $data){
        $resaurant = Restaurant::with('bankAccount')->where('id',$order->restaurant_id)->first();
        if($resaurant && $resaurant->bankAccount){
            $applicationfee = $resaurant->commission;
            if($resaurant->commission_type == 2){
                $applicationfee = ($order->total_amount * $applicationfee)/100;
            }
            $currency = 'USD';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $charge = Charge::create([
                'amount' => $order->total_amount * 100,
                'currency' => $currency,
                'source' => $data['card_id'],
                'customer' => $data['customer_id'],
                'destination' => $resaurant->bankAccount->bank_account_id,
                'application_fee' => $applicationfee * 100
            ]);
            if($charge->status == "succeeded"){
                $orderPayment = new OrderPayment();
                $orderPayment->order_id = $order->order_id;
                $orderPayment->order_code = $order->order_id;
                $orderPayment->token = $charge->id;
                $orderPayment->order_description = '';
                $orderPayment->amount = $order->total_amount;
                $orderPayment->currency_code = 'USD';
                $orderPayment->payment_status = 1;
                $orderPayment->expiry_month = $charge->payment_method_details->card->exp_month;
                $orderPayment->expiry_year = $charge->payment_method_details->card->exp_year;
                $orderPayment->card_type = $charge->payment_method_details->card->network;
                $orderPayment->masked_card_number = $charge->payment_method_details->card->last4;
                $orderPayment->save();
                return true;
            } else{
                return false;
            }
        }
        return false;
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

        if(Auth::user()->id){
            $card = SavedCard::where('id', '=', $request->input('card_id'))->first();

            SavedCard::where('id', '=', $request->input('card_id'))->delete();

            try {
                $apiKey = $this->getApiKey();
                Stripe::setApiKey($apiKey['secret_key']);
                $result = Customer::deleteSource(Auth::user()->stripe_customer_id,$card->token);
                if ($result->deleted) {
                    return response()->json(apiResponseHandler([], 'Success'));
                } else{
                    return response()->json(apiResponseHandler([], 'Failed', 400), 400);
                }
            } catch (ApiErrorException $e){
                return response(apiResponseHandler([], $e->getMessage(), 400), 400);
            }
        } else{
            return response()->json(apiResponseHandler([], 'Failed', 400), 400);
        }
    }

    public function walletTransaction($order, $data){
        try{
            $currency = 'USD';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $result = Charge::create([
                'amount' => $order->total_amount * 100,
                'currency' => $currency,
                'source' => $data['token'],
                'customer' => Auth::user()->stripe_customer_id
            ]);

            if ($result->status === 'succeeded') {
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
        } catch (ApiErrorException $e) {
            return false;
        }
    }

    public function makeGiftCardPayment($cardId, $amount){
        $card = SavedCard::where('id', '=', $cardId)->first();

        if($card){
            try {
                $currency = 'USD';
                $apiKey = $this->getApiKey();
                Stripe::setApiKey($apiKey['secret_key']);
                $result = Charge::create([
                    'amount' => $amount * 100,
                    'currency' => $currency,
                    'source' => $card->token,
                    'customer' => Auth::user()->stripe_customer_id
                ]);

                if ($result->status === 'succeeded') {
                    return true;
                }else{
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

    public function testWallet(){
        $apiKey = $this->getApiKey();
        Stripe::setApiKey($apiKey['secret_key']);
        $charge = Charge::create([
            'amount' =>  952,
            'currency' => 'USD',
            'source' => 'tok_1KtQRPJClpq6GgBHcrsdRfe7'
        ]);
        return response()->json($charge);
    }
}
