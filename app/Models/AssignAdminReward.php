<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed admin_reward_id
 * @property mixed user_id
 */
class AssignAdminReward extends Model
{
    protected $table = 'assign_admin_rewards';

    protected $fillable = ['admin_reward_id', 'user_id'];
}
