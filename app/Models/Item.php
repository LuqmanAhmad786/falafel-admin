<?php

namespace App\Models;

use App\Models\Favorite\FavoriteItem;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string restaurant_id
 * @property array|null|string item_name
 * @property array|null|string item_price
 * @property array|null|string item_image
 * @property array|null|string item_image_single
 * @property array|null|string item_thumbnail
 * @property array|null|string tax_rate
 * @property array|null|string item_description
 * @property array|null|string its_own
 * @property array|null|string complete_meal_of
 * @property array|null|string reference_item_id
 * @property array|null|string menu_type
 */
class Item extends Model
{
    protected $primaryKey = 'item_id';

    protected $table = 'items';

    protected $fillable = ['item_name', 'item_price', 'item_image', 'item_thumbnail', 'tax_rate', 'item_description',
        'its_own', 'complete_meal_of', 'restaurant_id','reference_item_id','item_image_single','clover_id','clover_stock','menu_type','tax_applicable','order_no','is_common','is_in_stock','tax_rate'];

    public function category()
    {
        return $this->hasMany(ItemCategory::class, 'item_id', 'item_id');
    }

    public function categoryName()
    {
        return $this->hasOne(ItemCategory::class, 'item_id', 'item_id');
    }

    public function favorite(){
        return $this->hasOne(FavoriteItem::class, 'item_id', 'item_id');
    }
}
