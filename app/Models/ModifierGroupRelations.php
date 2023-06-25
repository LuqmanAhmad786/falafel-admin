<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroupRelations extends Model
{
    protected $table = 'modifier_group_relations';

    protected $fillable = ['modifier_group_id', 'item_id'];
}
