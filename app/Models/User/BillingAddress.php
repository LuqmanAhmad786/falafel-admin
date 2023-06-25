<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    protected $table = 'billing_addresses';

    protected $fillable = [
        'user_id',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postcode',
        'phone'
    ];
}
