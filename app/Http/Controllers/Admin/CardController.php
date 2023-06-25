<?php

namespace App\Http\Controllers\Admin;

use App\Models\User\CardTransactionHistory;
use App\Models\User\GiftCard;
use App\Models\User\UserCard;
use Illuminate\Auth\Recaller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function loadUserCards(Request $request){
        $cards = UserCard::with(['user','giftCard'])
            ->orderBy('user_falafel_cards.id','DESC')
            ->paginate(10);
        return view('dashboard.cards.user-cards',compact('cards'));
        
    }

    public function loadGiftCards(Request $request){
        $cards = GiftCard::with(['card'])
            ->orderBy('gift_cards.id','DESC')
            ->paginate(10);
        return view('dashboard.cards.gift-cards',compact('cards'));
    }

    public function loadUserCard($id){
        $card = UserCard::with(['user','giftCard'])
            ->where('id', $id)->first();
        $transactions = CardTransactionHistory::where('falafel_card_id',$id)->orderBy('id','desc')->get();
        return view('dashboard.cards.user-card',compact('card','transactions'));
    }

    public function cardRecharge(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'card_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponseHandler([], $validator->errors()->first(), 400), 400);
        }

        $amount = $request->input('amount');
        $card_id = $request->input('card_id');

        $card = UserCard::find($card_id);
        $card->balance += $amount;
        $card->save();

        createCardTrxHistoryServer([
            'falafel_card_id' => $card_id,
            'action_type' => 'Recharged by System',
            'transaction_amount' => $amount
        ],0);
    }
}
