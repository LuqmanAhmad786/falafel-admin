<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPreference extends Model
{
    protected $table = 'subscription_preferences';

    protected $fillable = [
        'user_id',
        'email_subscription',
        'phone_number_subscription',
    ];
}
