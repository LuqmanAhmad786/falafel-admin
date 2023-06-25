<?php

namespace App\Models\Favorite;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string label_name
 * @property array|string|null added_by
 * @property mixed favorite_label_id
 */
class FavoriteLabel extends Model
{
    protected $primaryKey = 'favorite_label_id';

    protected $table = 'favorite_labels';

    protected $fillable = ['label_name', 'added_by'];
}
