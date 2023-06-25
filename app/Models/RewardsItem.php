<?php

namespace App\Models;

use App\Models\User\UserRewardItems;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string item_id
 * @property mixed restaurant_id
 * @property mixed is_enable
 * @property array|null|string flag
 * @property array|null|string category_id
 */
class RewardsItem extends Model
{
    protected $primaryKey = 'reward_item_id';

    protected $table = 'rewards_items';

    protected $fillable = ['item_id', 'restaurant_id', 'is_enable', 'flag', 'category_id'];

    public function item()
    {
        return $this->hasOne(Item::class, 'item_id', 'item_id');
    }

    public function redeemed()
    {
        return $this->hasOne(UserRewardItems::class, 'reward_item_id', 'reward_item_id');
    }
}
