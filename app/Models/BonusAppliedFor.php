<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed bonus_id
 * @property mixed user_id
 * @property mixed is_used
 */
class BonusAppliedFor extends Model
{
    protected $table = 'bonus_applied_for';

    protected $fillable = ['bonus_id', 'user_id', 'is_used'];

    public function bonus()
    {
        return $this->hasOne(Bonus::class, 'bonus_id', 'bonus_id');
    }
}
