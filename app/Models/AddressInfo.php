<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressInfo extends Model
{
    use HasFactory;

    protected $table = 'address_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'address',
        'warehouse_address',
    ];

    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
