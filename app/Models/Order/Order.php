<?php

namespace App\Models\Order;

use App\Models\Favorite\FavoriteOrder;
use App\Models\OrderPayment;
use App\Models\Restaurant;
use App\Models\UserRewards;
use App\OrderRefunds;
use App\User;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @property string reference_id
 * @property array|null|string user_id
 * @property array|null|string restaurant_id
 * @property array|null|string pickup_time
 * @property array|null|string total_amount
 * @property array|null|string status
 * @property mixed order_id
 * @property mixed id
 * @property float|int order_total
 * @property float|int total_tax
 * @property int preparation_time
 *  * @property array|null|string discount_amount
 * @property array|null|string coupon_id
 * @property array|null|string menu_id
 * @property array|null|string user_name
 * @property array|null|string user_email
 * @property array|null|string user_number
 * @property array|null|string address_line_1
 * * @property int|null|int bonus_id
 * @property array|string|null user_first_name
 * @property array|string|null user_last_name
 * @property array|string|null order_type
 * @property array|string|null delivery_address
 * @property array|string|null order_device
 * @property array|string|null delivery_notes
 * @property array|string|null firebase_token
 * @property array|string|null pickup_date
 * @property array|string|null delivery_fee
 * @property array|Integer|null is_server_order
 * @property array|Integer|null server_user_id
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    protected $fillable = ['reference_id', 'user_id', 'restaurant_id', 'pickup_time', 'total_amount', 'status',
        'total_tax', 'order_total',
        'discount_amount',
        'user_name',
        'user_first_name',
        'user_last_name',
        'user_email',
        'user_number',
        'coupon_id'];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function userDetails()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function restaurantDetails()
    {
        return $this->hasOne(Restaurant::class, 'id', 'restaurant_id');
    }

    public function favoriteOrder()
    {
        return $this->hasOne(FavoriteOrder::class, 'order_id', 'order_id');
    }

    public function userReward()
    {
        return $this->hasOne(UserRewards::class, 'order_id', 'order_id');
    }

    public function feedback()
    {
        return $this->hasOne(OrderFeedback::class, 'order_id', 'order_id');
    }

    public function transaction()
    {
        return $this->hasOne(OrderPayment::class, 'order_id', 'order_id');
    }

    public function refund()
    {
        return $this->hasMany(OrderRefunds::class, 'order_id', 'order_id');
    }
}
