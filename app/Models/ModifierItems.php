<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed modifier_group_id
 * @property mixed item_id
 * @property mixed added_from
 * @property mixed item_name
 * @property mixed item_price
 * @property mixed item_description
 * @property mixed item_image
 * @property mixed its_own
 * @property mixed  order_no
 */
class ModifierItems extends Model
{
    protected $table = 'modifier_items';

    protected $fillable = ['modifier_group_id', 'item_id', 'added_from', 'order_no',
        'item_name',
        'item_price',
        'item_description',
        'its_own',
        'item_image',
        'clover_id'];

    public function meals()
    {
        return $this->hasMany(MenuCategory::class, 'item_id', 'item_id');
    }

    public function modifierMenus()
    {
        return $this->hasMany(Item::class, 'item_id', 'item_id');
    }

    public function modifierGroup(){
        return $this->hasOne(ModifierGroup::class,'modifier_group_id','modifier_group_id');
    }


}
