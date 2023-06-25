<?php

namespace App\Models\User;

use App\Models\Card;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    protected $table = 'gift_cards';

    protected $fillable = [
        'user_id',
        'card_id',
        'sender_name',
        'sender_email',
        'receiver_name',
        'receiver_email',
        'message',
        'amount',
        'card_number',
        'card_code',
        'is_redeemed',
    ];

    public function card(){
        return $this->hasOne(Card::class, 'id','card_id');
    }
}
