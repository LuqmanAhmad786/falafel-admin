<?php

namespace App\Models\Order;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed order_id
 * @property mixed item_id
 * @property mixed item_count
 * @property mixed item_price
 * @property mixed order_detail_id
 * @property mixed id
 */
class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = ['order_id', 'item_id', 'item_price'];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_detail_id', 'order_detail_id');
    }

    public function item()
    {
        return $this->hasOne(Item::class, 'item_id', 'item_id');
    }
}
