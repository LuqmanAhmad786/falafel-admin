<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloverItem extends Model
{
    protected $primaryKey = 'item_id';

    protected $table = 'clover_items';

    protected $fillable = ['item_name', 'item_price', 'item_image', 'item_thumbnail', 'tax_rate', 'item_description',
        'its_own', 'complete_meal_of', 'restaurant_id','reference_item_id','item_image_single','clover_id','clover_stock','menu_type','tax_applicable'];

    public function category()
    {
        return $this->hasMany(ItemCategory::class, 'item_id', 'item_id');
    }

    public function categoryName()
    {
        return $this->hasOne(ItemCategory::class, 'item_id', 'item_id');
    }
}
