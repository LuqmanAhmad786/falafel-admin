<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed item_id
 * @property mixed cart_list_id
 * @property mixed cart_item_id
 * @property mixed bonus_id
 * @property mixed menu_id
 */
class CartItem extends Model
{
    protected $primaryKey = 'cart_item_id';

    protected $table = 'cart_items';

    protected $fillable = ['receiver_name', 'item_id', 'item_count', 'cart_list_id', 'item_flag', 'bonus_id', 'menu_id'];

    public function details()
    {
        return $this->hasMany(CartDetail::class, 'cart_item_id', 'cart_item_id');
    }
}
