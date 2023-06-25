<?php

namespace App\Http\Controllers\API;

use App\Models\Card;
use App\Models\CardCategory;
use App\Models\SavedCard;
use App\Models\User\CardTransactionHistory;
use App\Models\User\GiftCard;
use App\Models\User\UserCard;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class CardController extends Controller
{
    public function getCards()
    {
        $cards = CardCategory::with(['card'])->get();
        return response()->json(apiResponseHandler($cards, 'success', 200));
    }

    public function getCardById($id)
    {
        $card = Card::find($id);
        return response()->json(apiResponseHandler($card, 'success', 200));
    }

    public function featuredCards()
    {
        $cards = Card::where('is_featured', 1)->get();
        return response()->json(apiResponseHandler($cards, 'success', 200));
    }

    public function purchaseFalafelCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('cards', 'id')
            ],
            'payment_card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')
            ],
            'amount' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $amount = $request->input('amount');
        if($amount > 0){
            $paymentController = new BtPaymentController();

            $paymentStatus = $paymentController->makeGiftCardPayment($request->input('payment_card_id'), $amount);

            $card = Card::find($request->input('card_id'));
            if($paymentStatus){
                UserCard::create([
                    'user_id' => Auth::id(),
                    'unique_id' => Uuid::uuid4(),
                    'card_number' => round(microtime(true)*1000),
                    'gift_card_id' => $request->input('card_id'),
                    'balance' => $amount,
                    'card_nickname' => 'My Card(' .$card->id .')'
                ]);
                return response()->json(apiResponseHandler([], 'Card purchased successfully.', 200), 200);
            }else{
                return response()->json(apiResponseHandler([], 'Something wrong with payment. Please try again or try with another card.', 400), 400);
            }
        }
        return response()->json(apiResponseHandler([], 'Please try again.', 400), 400);
    }

    public function purchaseGiftCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('cards', 'id')
            ],
            'payment_card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')
            ],
            'gift_amount' => 'required',
            'recipient_name' => 'required',
            'recipient_email' => 'required|email',
            'sender_name' => 'required',
            'sender_email' => 'required|email',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $amount = $request->input('gift_amount');
        if($amount > 0){
            $paymentController = new StripePaymentController();

            $paymentStatus = $paymentController->makeGiftCardPayment($request->input('payment_card_id'), $amount);

            $card = Card::find($request->input('card_id'));
            if($paymentStatus){
                $passcode = rand(100000,999999);
                $cardNumber = round(microtime(true)*1000);
                $giftC = GiftCard::create([
                    'user_id' => Auth::id(),
                    'card_id' => $card->id,
                    'sender_name' => $request->input('sender_name'),
                    'sender_email' => $request->input('sender_email'),
                    'receiver_name' => $request->input('recipient_name'),
                    'receiver_email' => $request->input('recipient_email'),
                    'message' => $request->input('message'),
                    'amount' => $request->input('gift_amount'),
                    'card_number' => $cardNumber,
                    'card_code' => md5($passcode),
                    'is_redeemed' => 0,
                ]);
                $template = view('email-templates.gift-card', [
                    'name' => $request->input('recipient_name'),
                    'sender' => $request->input('sender_name'),
                    'gift_card_number' => $cardNumber,
                    'gift_card_code' => $passcode
                ])->render();
                sendEmailFalafel($template, $request->input('recipient_email'), 'Falafel Corner gift card received');
                $buyerTemplate = view('email-templates.new-templates.egift-card-receipt', [
                    'name' => $request->input('sender_name'),
                    'gift_card_image' => $card->card_image,
                    'gift_card_number' => $giftC->id,
                    'recipient_email' => $request->input('recipient_email'),
                    'amount' => $request->input('gift_amount'),
                    'tax' => '0.00',
                ])->render();
                $email = Auth::user()->email;/*'yashdeep.qualwebs@gmail.com';*/
                sendEmailFalafel($buyerTemplate, $email, 'Your Falafel Corner eGift Card receipt is enclosed');

                return response()->json(apiResponseHandler([], 'Card purchased successfully.', 200), 200);
            }else{
                return response()->json(apiResponseHandler([], 'Something wrong with payment. Please try again or try with another card.', 400), 400);
            }
        }
        return response()->json(apiResponseHandler([], 'Please try again.', 400), 400);
    }

    public function getFalafelCards(){
        $cards = UserCard::with(['giftCard'])->where('user_id',Auth::user()->id)->get();
        return response()->json(apiResponseHandler($cards, '', 200), 200);
    }

    public function getFalafelCardById($id){
        $card = UserCard::with(['giftCard'])->where('unique_id' , $id)->first();
        return response()->json(apiResponseHandler($card, '', 200), 200);
    }

    public function addMoneyCard(Request $request){
        $validator = Validator::make($request->all(), [
            'card_id' => [
                'required',
                Rule::exists('user_falafel_cards', 'unique_id')
            ],
            'payment_card_id' => [
                'required',
                Rule::exists('saved_cards', 'id')
            ],
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $amount = $request->input('amount');

        if($amount > 0){
            $paymentController = new StripePaymentController();

            $paymentStatus = $paymentController->makeGiftCardPayment($request->input('payment_card_id'), $amount);

            if($paymentStatus){
                $card = UserCard::where('unique_id', $request->input('card_id'))->first();

                $card->balance = $card->balance + $amount;
                $card->save();

                createCardTrxHistory([
                    'falafel_card_id' => $card->id,
                    'action_type' => 'reloaded',
                    'transaction_amount' => $amount
                ]);
                $savedCardAddress = SavedCard::with('billingAddress')->where('id',$request->input('payment_card_id'))->first();
                $template = view('email-templates.new-templates.card-reload', [
                    'name' => Auth::user()->first_name,
                    'order_id' => uniqid(),
                    'order_details' => $savedCardAddress,
                    'fc_cardname' => $card->card_nickname,
                    'fc_carddesc' => 'Falafel Corner Card Reload',
                    'fc_cardqty' => 1,
                    'fc_cardunitP' => $amount,
                    'fc_tax' => '0.00',
                    'fc_shipping' => '0.00',
                    'fc_cardTotalP' => $amount,
                ]);

                $email = Auth::user()->email;//'yashdeep.qualwebs@gmail.com';
                sendEmailFalafel($template, $email, 'Thank You! Falafel Corner Card Reload Order');
                return response()->json(apiResponseHandler([], 'Money added successfully.', 200), 200);
            }else{
                return response()->json(apiResponseHandler([], 'Something wrong with payment. Please try again or try with another card.', 400), 400);
            }
        }
    }

    public function updateCardNickname(Request $request){
        $validator = Validator::make($request->all(), [
            'card_nickname' => 'required',
            'card_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        UserCard::where('unique_id' , $request->input('card_id'))
            ->where('user_id', Auth::user()->id)
            ->update([
                'card_nickname' => $request->input('card_nickname')
            ]);

        return response()->json(apiResponseHandler([], 'Nickname updated.', 200), 200);
    }

    public function updateCardDefaultStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'is_default' => 'required',
            'card_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        UserCard::where('user_id', Auth::user()->id)
            ->update([
                'is_default' => 0
            ]);

        UserCard::where('unique_id' , $request->input('card_id'))
            ->where('user_id', Auth::user()->id)
            ->update([
                'is_default' => $request->input('is_default')
            ]);

        return response()->json(apiResponseHandler([], 'This card is default now.', 200), 200);
    }

    public function deleteFalafelCard($id){
        UserCard::where('unique_id' , $id)
            ->where('user_id', Auth::user()->id)
            ->delete();
        return response()->json(apiResponseHandler([], 'Card deleted.', 200), 200);
    }

    public function transferFalafelAmount(Request $request){
        $validator = Validator::make($request->all(), [
            'feed_card_id' => 'required',
            'card_id' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $falafelCard = UserCard::where('unique_id', $request->input('feed_card_id'))
            ->where('user_id', Auth::user()->id)
            ->first();

        $amount = $request->input('amount');
        if($falafelCard){
            if($falafelCard->balance < $amount){
                return response()->json(apiResponseHandler([], 'Insufficient balance in card.', 400), 400);
            }

            $falafelCard->balance = $falafelCard->balance - $amount;
            $falafelCard->save();

            $anotherCard = UserCard::find($request->input('card_id'));
            $anotherCard->balance = $anotherCard->balance + $amount;
            $anotherCard->save();

            createCardTrxHistory([
                'falafel_card_id' => $falafelCard->id,
                'action_type' => 'Transfer[From] XXXX XXXX XXXX '.$falafelCard->gift_card_id,
                'transaction_amount' => $amount
            ]);

            createCardTrxHistory([
                'falafel_card_id' => $anotherCard->id,
                'action_type' => 'Transfer[To] XXXX XXXX XXXX '. $anotherCard->gift_card_id,
                'transaction_amount' => $amount
            ]);

            $template = view('email-templates.new-templates.balance-transfer',[
                'amount' => $amount,
                'card_1' => "XXXX XXXX XXXX ".$falafelCard->gift_card_id,
                'card_2' => "XXXX XXXX XXXX ".$anotherCard->gift_card_id,
            ]);

            $email = Auth::user()->email;/*'yashdeep.qualwebs@gmail.com';*/
            sendEmailFalafel($template, $email, 'Confirmation of Falafel Corner Card Balance Transfer');
            return response()->json(apiResponseHandler([], 'Amount transferred.', 200), 200);
        }else{
            return response()->json(apiResponseHandler([], 'Invalid card.', 400), 400);
        }
    }

    public function redeemGiftCard(Request $request){
        $validator = Validator::make($request->all(), [
            'card_number' => 'required',
            'card_security_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $hashedCode = md5($request->input('card_security_code'));

        $validate = GiftCard::where('card_number', $request->input('card_number'))
            ->where('card_code', $hashedCode)
            ->where('is_redeemed', 0)
            ->first();

        if($validate){
            $card = Card::find($validate->card_id);
            UserCard::create([
                'user_id' => Auth::id(),
                'unique_id' => Uuid::uuid4(),
                'card_number' => $validate->card_number,
                'gift_card_id' => $card->id,
                'balance' => $validate->amount,
                'card_nickname' => $card->card_name,
                'non_transferable' => 1
            ]);

            GiftCard::where('id', $validate->id)->update(['is_redeemed' => 1]);

            return response()->json(apiResponseHandler([], 'Gift card redeemed successfully.', 200), 200);
        }else{
            return response()->json(apiResponseHandler([], 'Invalid card details.', 400), 400);
        }
    }

    public function getCardHistory(){
        $data = CardTransactionHistory::with(['card'])
            ->where('user_id',Auth::user()->id)
            ->orderBy('id','desc')
            ->get();

        return response()->json(apiResponseHandler($data, '', 200), 200);
    }
}
