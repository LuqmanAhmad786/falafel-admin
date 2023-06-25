<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = ['menu_id', 'item_id', 'category_id'];

    public function meal()
    {
        return $this->hasOne(Item::class, 'item_id', 'item_id');
    }
}
