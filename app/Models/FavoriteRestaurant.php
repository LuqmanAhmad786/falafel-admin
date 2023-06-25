<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string restaurant_id
 * @property array|null|string user_id
 */
class FavoriteRestaurant extends Model
{
    protected $primaryKey = 'favorite_res_id';

    protected $table = 'favorite_restaurants';

    protected $fillable = ['restaurant_id', 'user_id'];
}
