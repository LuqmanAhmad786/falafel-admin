<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserMembership extends Model
{
    public function membership(){
        return $this->hasOne(Membership::class, 'id','membership_id');
    }
}
