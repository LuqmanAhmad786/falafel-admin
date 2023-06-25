<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int order_id
 * @property int user_id
 * @property int type
 * @property int total_rewards
 * @property int month
 */
class UserRewards extends Model
{
    protected $primaryKey = 'reward_id';

    protected $table = 'user_rewards';

    protected $fillable = ['order_id', 'user_id', 'total_rewards', 'month', 'type'];

    public function rewards()
    {
        return $this->hasMany(UserRewards::class, 'month', 'month');
    }
}
