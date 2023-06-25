<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompleteMeals extends Model
{
    protected $table = 'complete_meals';

    protected $fillable = ['menu_id', 'category_id', 'item_id'];

    public function meal()
    {
        return $this->hasOne(Item::class, 'item_id', 'item_id');
    }
}
