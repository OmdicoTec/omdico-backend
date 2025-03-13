<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class purchase_offers extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'details',
        'proposed_price',
        'status',
        'image',
        'suggested_date',
        'confirmed_date',
        'is_winner',
        'items',
        'have_tax'
    ];
    // protected $hidden = ['user_id'];

    // Relationships for purchase_requests
    public function purchase_request()
    {
        return $this->belongsTo(purchase_requests::class, 'purchase_request_id');
    }
    // Relationships for users
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice that owns the purchase_offers.
     */
    public function invoice()
    {
        return $this->hasOne(invoice::class); // TODO: hasOne
    }

    // Accessor for the 'items' attribute
    public function getItemsAttribute($value)
    {
        return json_decode($value, true);
    }
}
