<?php

namespace App\Models;

use App\Models\Order\OrderFeedback;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string generated_order_id
 * @property string user_id
 * @property array|null|string restaurant_id
 * @property array|null|string pickup_time
 * @property array|null|string total_amount
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'generated_order_id',
        'user_id',
        'restaurant_id',
        'pickup_time',
        'total_amount',
        'status'
    ];

    public function feedback()
    {
        return $this->hasOne(OrderFeedback::class, 'order_id', 'order_id');
    }
}
