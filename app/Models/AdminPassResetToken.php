<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPassResetToken extends Model
{
    protected $fillable = [
        'token','valid_till'
    ];
}
