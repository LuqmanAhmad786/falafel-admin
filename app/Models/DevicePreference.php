<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePreference extends Model
{
    protected $table = 'device_preferences';

    protected $fillable = ['user_id', 'push_notification'];
}
