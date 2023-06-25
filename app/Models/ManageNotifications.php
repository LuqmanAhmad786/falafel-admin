<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|string|null type_id
 * @property array|string|null type_name
 * @property array|string|null message_text
 */
class ManageNotifications extends Model
{
    protected $table = 'manage_notifications';

    protected $fillable = [
        'type_id',
        'type_name',
        'message_text',
    ];
}
