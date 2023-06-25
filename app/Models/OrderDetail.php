<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed order_id
 * @property mixed menu_id
 * @property mixed price
 */
class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = ['order_id', 'menu_id', 'price'];
}
