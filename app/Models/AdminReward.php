<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string name
 * @property array|null|string description
 * @property array|null|string expiry
 * @property array|null|string item_id
 * @property int unique_key
 * @property array|null|string reward_point
 * @property array|null|string admin_reward_id
 */
class AdminReward extends Model
{
    protected $primaryKey = 'admin_reward_id';

    protected $table = 'admin_rewards';

    protected $fillable = ['name', 'description', 'expiry', 'item_id', 'reward_point', 'unique_key'];

    public function users()
    {
        return $this->hasMany(AssignAdminReward::class, 'admin_reward_id', 'admin_reward_id');
    }
}
