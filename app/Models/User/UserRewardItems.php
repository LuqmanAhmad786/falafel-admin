<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserRewardItems extends Model
{
    protected $fillable = ['user_id','reward_item_id'];
}
