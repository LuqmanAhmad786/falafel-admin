<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardCategory extends Model
{
    protected $fillable = ['category_name'];

    public function card(){
        return $this->hasMany(Card::class, 'card_category_id','id');
    }
}
