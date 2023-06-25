<?php

namespace App;

use App\Models\Order\Order;
use App\Models\UserRewards;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @property array|string|null last_name
 * @property array|string|null first_name
 * @property array|string|null email
 * @property array|string|null mobile
 * @property string password
 * @property array|string|null zip_code
 * @property false|int date_of_birth
 * @property mixed id
 * @property array|string|null google_id
 * @property array|string|null facebook_id
 * @property array|string|null customer_id
 * @property array|string|null is_server_user
 * @property array|string|null assigned_restaurant
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile', 'password', 'zip_code', 'date_of_birth', 'customer_id',
        'google_id',
        'facebook_id',
        'latitude',
        'longitude',
        'restaurant_preference',
        'is_account_deleted',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function reward()
    {
        return $this->hasMany(UserRewards::class, 'user_id', 'id');
    }
}
