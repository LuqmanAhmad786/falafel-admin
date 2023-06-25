<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|string|null bonus_type
 * @property array|string|null bonus_condition_type
 * @property array|string|null bonus_name
 * @property array|string|null notification_text
 * @property array|string|null description
 * @property array|string|null term_and_condition
 * @property array|string|null bonus_orders_no
 * @property array|string|null bonus_start_date
 * @property array|string|null bonus_end_date
 * @property array|string|null bonus_start_hour
 * @property array|string|null bonus_end_hour
 * @property array|string|null bonus_plates_no
 * @property array|string|null bonus_user_points
 * @property array|string|null bonus_expiry
 * @property array|string|null bonus_free_item_id
 * @property array|string|null bonus_extra_point
 * @property array|string|null bonus_points_multiplier
 * @property array|string|null bonus_discount
 * @property mixed bonus_id
 */
class Bonus extends Model
{
    protected $primaryKey = 'bonus_id';

    protected $table = 'bonus';

    protected $fillable = [
        'bonus_type',
        'bonus_condition_type',
        'bonus_name',
        'bonus_expiry',
        'notification_text',
        'description',
        'term_and_condition',
        'bonus_free_item_id',
        'bonus_extra_point',
        'bonus_points_multiplier',
        'bonus_discount',
        'bonus_orders_no',
        'bonus_orders_no',
        'bonus_orders_no',
        'bonus_start_date',
        'bonus_end_date',
        'bonus_start_hour',
        'bonus_end_hour',
        'bonus_plates_no',
        'bonus_user_points',
    ];

    public function appliedFor()
    {
        return $this->hasMany(BonusAppliedFor::class, 'bonus_id', 'bonus_id');
    }

}
