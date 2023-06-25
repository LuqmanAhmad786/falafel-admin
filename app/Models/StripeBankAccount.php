<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeBankAccount extends Model
{
    protected $fillable = [
        'restaurant_id','bank_account_id', 'bank_name', 'account_number','routing_number'
    ];

    public static $bankAccountValidations = [
        'account_number' => 'required',
        'routing_number' => 'required',
        'account_holder_name' => 'required'
    ];
}
