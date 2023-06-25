<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestCustomer extends Model
{
    protected $fillable = ['guest_first_name', 'guest_last_name', 'guest_email', 'guest_mobile'];

}
