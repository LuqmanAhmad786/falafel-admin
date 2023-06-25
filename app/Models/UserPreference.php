<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string restaurant_id
 * @property mixed user_id
 */
class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = ['user_id', 'restaurant_id'];
}
