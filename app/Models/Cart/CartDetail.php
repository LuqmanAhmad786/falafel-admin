<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed  cart_item_id
 * @property mixed  item_id
 * @property mixed  modifier_group_id
 * @property mixed  item_count
 */
class CartDetail extends Model
{
    protected $table = 'cart_details';

    protected $fillable = ['cart_item_id','modifier_group_id','item_id','item_count'];
}
