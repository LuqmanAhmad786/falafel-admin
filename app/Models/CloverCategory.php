<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloverCategory extends Model
{
    protected $primaryKey = 'category_id';

    protected $table = 'clover_categories';

    protected $fillable = ['restaurant_id', 'category_name','order_no','clover_id'];

    public function menu()
    {
        return $this->hasMany(MenuCategory::class, 'category_id', 'category_id');
    }

    public function sideMenu()
    {
        return $this->hasMany(ItemCategory::class, 'category_id', 'category_id');
    }
}
