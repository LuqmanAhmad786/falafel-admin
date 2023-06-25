<?php

namespace App\Models\Favorite;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string order_id
 * @property mixed user_id
 * @property array|null|string favorite_label_id
 */
class FavoriteOrder extends Model
{
    protected $table = 'favorite_orders';

    protected $fillable = ['order_id', 'user_id', 'favorite_label_id'];
}
