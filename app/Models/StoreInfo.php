<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreInfo extends Model
{
    use HasFactory;

    protected $table = 'store_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'store_name',
        'phone_number',
        'working_days',
        'website',
        'about_store',
        'seller_code',
        'activity_area',
        'category',
    ];

    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
