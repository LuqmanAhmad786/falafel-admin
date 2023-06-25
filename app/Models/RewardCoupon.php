<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RewardCoupon extends Model
{
    protected $primaryKey = 'coupon_id';

    protected $table = 'reward_coupons';

    protected $fillable = ['user_id', 'expiry', 'status', 'coupon_type','admin_reward_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
