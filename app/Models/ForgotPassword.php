<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string email
 * @property string otp
 * @property int expiry
 */
class ForgotPassword extends Model
{
    protected $table = 'forgot_passwords';

    protected $fillable = ['email', 'otp', 'expiry'];
}
