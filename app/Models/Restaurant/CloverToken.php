<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class CloverToken extends Model
{
    protected $table = 'clover_tokens';

    protected $fillable = [
        'app_id',
        'auth_token',
        'merchant_id'
    ];
}
