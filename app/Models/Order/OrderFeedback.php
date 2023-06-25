<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class OrderFeedback extends Model
{
    protected $fillable = ['order_id', 'user_id', 'feedback', 'review'];
}
