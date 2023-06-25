<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\SavedCard;
use App\Models\StripeBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Stripe\Account;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createStripeAccount(Request $request)
    {
        $restaurant = Restaurant::where('id', $request->input('restaurant_id'))->first();
        if ($restaurant) {
            $accountHolder = $request['account_holder_name'];
            $currency = 'USD';
            $ssn = '0000';
            $apiKey = $this->getApiKey();
            Stripe::setApiKey($apiKey['secret_key']);
            $accountArray = ['country' => "US",
                'type' => 'custom',
                "requested_capabilities" => [
                    "card_payments",
                    "transfers"
                ],
                "business_type" => "company",
                "tos_acceptance" => array(
                    'date' => time(),
                    'ip' => $_SERVER['REMOTE_ADDR']
                )
            ];
            try {
                $account = Account::create($accountArray);
                $externalAccount = $account->external_accounts->create(
                    [
                        "external_account" => [
                            "object" => "bank_account",
                            "country" => "US",
                            "currency" => $currency,
                            "account_holder_name" => ucwords($accountHolder),
                            "routing_number" => $request['routing_number'],
                            "account_holder_type" => "individual",
                            "account_number" => $request['account_number']
                        ]
                    ]
                );
                $stripeAccount = StripeBankAccount::updateOrCreate(['restaurant_id' => $request['restaurant_id']],[
                    'restaurant_id' => $restaurant->id,
                    'bank_account_id' => $account->id,
                    'bank_name' => "",
                    'account_number' => $request['account_number'],
                    'routing_number' => $request['routing_number']
                ]);
                return response()->json(apiResponseHandler($stripeAccount,'Stripe account saved successfully.',200),200);
            } catch (Exception $exception) {
                return response()->json(apiResponseHandler([], $exception->getMessage(), 400), 400);
            }
        } else {
            return response()->json(apiResponseHandler([], 'Invalid restaurant selected. Please select valid restaurant.', 400), 400);
        }
    }

    public function retrieveBankInfo($restaurant){
        $bankAccount = StripeBankAccount::where('restaurant_id',$restaurant->id)->first();
        $apiKey = $this->getApiKey();
        Stripe::setApiKey($apiKey['secret_key']);
        $bankInfo = Account::retrieve($bankAccount->bank_account_id);
        $arrData = [
            'bankinfo' => $bankInfo,
            'restaurant' => $restaurant,
        ];
        return response()->json(apiResponseHandler($arrData,'Stripe account info.',200),200);
    }

    public function makeOrderPayment($cardId, $order){
        $card = SavedCard::where('id', '=', $cardId)->first();
        if($card){

        } else{
            return false;
        }
    }

    public function getApiKey()
    {
        $keyArray = Config::get('stripe');
        return $keyArray['sandbox'];
    }
}
