<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed order_detail_id
 * @property mixed modifier_group_id
 * @property mixed item_id
 * @property mixed item_count
 * @property mixed item_price
 */
class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = ['order_detail_id', 'modifier_group_id', 'item_id', 'item_count', 'item_price'];
}
