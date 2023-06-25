<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $fillable = ["order_id", "order_code", "token", "order_description", "amount", "currency_code",
        "payment_status", "type", "name", "expiry_month", "expiry_year", "issue_number", "start_month",
        "start_year", "card_type", "masked_card_number", "card_scheme_type", "card_scheme_name", "card_issuer",
        "country_code", "card_class", "card_product_type_desc_non_contactless",
        "card_product_type_desc_contactless", "prepaid", "is3_ds_order", "authorize_only",
        "customer_order_code", "environment", "avs_result_code", "cvc_result_code"];
}
