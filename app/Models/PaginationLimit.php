<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginationLimit extends Model
{
    protected $table = 'pagination_limits';

    public $fillable = ['limit'];
}
