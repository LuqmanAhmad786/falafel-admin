<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';

    protected $fillable = ['category_id', 'menu_id', 'order_no'];

    public function menus()
    {
        return $this->hasMany(ItemCategory::class, 'category_id', 'category_id');
    }

    public function category(){
        return $this->hasOne(Category::class, 'category_id', 'category_id');
    }
}
