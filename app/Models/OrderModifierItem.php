<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed order_id
 * @property mixed modifier_id
 * @property mixed modifier_item_id
 * @property mixed count
 * @property mixed price
 */
class OrderModifierItem extends Model
{
    protected $table = 'order_modifier_items';

    protected $fillable = ['order_id', 'modifier_id', 'modifier_item_id', 'count', 'price'];
}
