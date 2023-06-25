<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOfflineDate extends Model
{
    protected $table = 'restaurant_offline_dates';

    protected $fillable = [
        'restaurant_id',
        'start_date',
        'end_date',
        'offline_message'
    ];
}
