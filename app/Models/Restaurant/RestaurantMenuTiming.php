<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class RestaurantMenuTiming extends Model
{
    protected $table = 'restaurant_menu_timings';

    protected $fillable = [
        'restaurant_menu_id',
        'day',
        'from_1',
        'to_1',
        'from_2',
        'to_2'
    ];
}
