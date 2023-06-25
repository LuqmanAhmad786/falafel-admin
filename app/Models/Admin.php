<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property array|null|string name
 * @property array|null|string email
 * @property array|null|string assigned_location
 * @property array|null|string password
 * @property array|null|string type
 */
class Admin extends Authenticatable
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'assigned_location'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function location()
    {
        return $this->hasOne(Restaurant::class, 'id', 'assigned_location');
    }
}
