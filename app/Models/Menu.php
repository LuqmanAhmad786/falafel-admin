<?php

namespace App\Models;

use App\Models\Restaurant\RestaurantMenuTiming;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string restaurant_id
 * @property array|null|string menu_name
 * @property array|null|string from
 * @property array|null|string to
 * @property array|null|string reference_id_text
 */
class Menu extends Model
{
    protected $primaryKey = 'menu_id';

    protected $table = 'menus';

    protected $fillable = ['restaurant_id', 'menu_name', 'from', 'to','reference_id_text'];

    public function categories()
    {
        return $this->hasMany(MenuCategory::class, 'menu_id', 'menu_id');
    }

    public function meals()
    {
        return $this->hasMany(Item::class, 'complete_meal_of', 'menu_id');
    }

    public function completeMeal()
    {
        return $this->hasMany(CompleteMeals::class, 'menu_id', 'menu_id');
    }

    public function timings()
    {
        return $this->hasMany(RestaurantMenuTiming::class, 'restaurant_menu_id', 'menu_id');
    }
}
