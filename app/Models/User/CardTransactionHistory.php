<?php

namespace App\Models\User;

use App\Models\Card;
use Illuminate\Database\Eloquent\Model;

class CardTransactionHistory extends Model
{
    protected $fillable = [
      'user_id',
      'falafel_card_id',
      'action_type',
      'transaction_amount',
    ];

    protected $appends = ['unix_created_at'];

    public function card(){
        return $this->hasOne(UserCard::class,'id','falafel_card_id');
    }

    public function getUnixCreatedAtAttribute(){
        return strtotime($this->attributes['created_at']);
    }
}
