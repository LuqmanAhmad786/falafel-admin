<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string restaurant_id
 * @property array|null|string modifier_group_name
 * @property array|null|string item_exactly
 * @property array|null|string item_range_from
 * @property array|null|string item_range_to
 * @property array|null|string item_maximum
 * @property array|null|string single_item_maximum
 * @property array|string|null order_no
 * @property array|string|null modifier_group_id
 */
class ModifierGroup extends Model
{
    protected $primaryKey = 'modifier_group_id';

    protected $table = 'modifier_groups';

    protected $fillable = ['modifier_group_name', 'item_exactly', 'item_range_from', 'item_range_to', 'item_maximum',
        'single_item_maximum', 'restaurant_id','order_no','modifier_group_identifier','clover_id'];

    public function items()
    {
        return $this->hasMany(ModifierItems::class, 'modifier_group_id', 'modifier_group_id');
    }
}
