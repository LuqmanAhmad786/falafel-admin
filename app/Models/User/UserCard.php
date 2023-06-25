<?php

namespace App\Models\User;

use App\Models\Card;
use App\User;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    protected $table = 'user_falafel_cards';

    protected $fillable = [
      'user_id',
      'unique_id',
      'gift_card_id',
      'balance',
      'is_default',
      'auto_load',
      'lost_reported',
        'card_nickname',
        'non_deletable',
        'non_transferable',
        'card_number',
    ];

    public function giftCard(){
        return $this->hasOne(Card::class, 'id','gift_card_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
