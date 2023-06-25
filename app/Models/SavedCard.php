<?php

namespace App\Models;

use App\Models\User\BillingAddress;
use Illuminate\Database\Eloquent\Model;

class SavedCard extends Model
{
    protected $fillable = [
        "user_id",
        "token",
        "name",
        "expiry_month",
        "expiry_year",
        "card_type",
        "masked_card_number",
        "is_default",
        'billing_address_id',
        'nickname',
        'stripe_customer_id',
        'payment_method'
    ];

    public function billingAddress(){
        return $this->hasOne(BillingAddress::class,'id','billing_address_id');
    }
}
