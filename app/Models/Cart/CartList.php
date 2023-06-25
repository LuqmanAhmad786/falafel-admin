<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string cart_id
 * @property array|null|string user_id
 * @property array|null|string total_tax
 * @property array|null|string order_total
 * @property array|null|string total_amount
 * @property array|null|string menu_id
 * @property array|null|string restaurant_id
 * @property array|null|string discount_amount
 * @property array|null|string coupon_id
 * @property array|null|string delivery_fee
 */
class CartList extends Model
{
    protected $primaryKey = 'cart_list_id';

    protected $table = 'cart_lists';

    protected $fillable = ['user_id', 'cart_id', 'total_tax', 'order_total', 'total_amount', 'menu_id',
        'restaurant_id',
        'discount_amount',
        'coupon_id',
        'delivery_fee'
    ];
}
