<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
      'user_id',
      'address_line_1',
      'address_line_2',
      'city',
      'state',
      'postcode',
        'latitude',
        'longitude',
      'contact_person',
      'contact_number',
      'order_note',
      'is_default'
    ];
}
