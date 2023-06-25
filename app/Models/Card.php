<?php

namespace App\Models;

use App\Models\User\GiftCard;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'card_category_id',
        'card_name',
        'card_image',
        'card_amount',
        'card_type'
    ];

    public function giftCard(){
        return $this->hasOne(GiftCard::class,'card_id','id');
    }
}
