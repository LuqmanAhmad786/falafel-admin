<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string name
 * @property array|null|string address
 * @property array|null|string contact_number
 * @property array|null|string restaurant_preference
 * @property array|null|string is_opened
 */
class Restaurant extends Model
{
    protected $table = 'restaurants';

    protected $fillable = ['name', 'slug', 'category_emoji', 'address', 'contact_number', 'additional_info', 'latitude',
        'longitude', 'restaurant_preference', 'is_opened', 'tax_rate', 'timezone','clover_mid','clover_api_key','clover_payment_api_key','clover_order_type_id','time_interval','clover_payment_api_token','clover_employee_id','clover_tender_id','emails','about','background_image', 'is_comingsoon',
        'commission_type', 'commission','pickup_time','preparation_time','status'];

    public function favorite()
    {
        return $this->hasOne(FavoriteRestaurant::class, 'restaurant_id', 'id');
    }

    public function menu(){
        return $this->hasOne(Menu::class, 'restaurant_id','id');
    }

    public function bankAccount(){
        return $this->hasOne(StripeBankAccount::class,'restaurant_id','id');
    }
}
