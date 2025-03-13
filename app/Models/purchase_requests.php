<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class purchase_requests extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'title',
        'category',
        'details',
        'amount',
        'quality',
        'delivery_date',
        'active_time',
        'proposed_price',
        'status',
        'features',
        'image',
        'user_id', // foreignId
        'purchase_offer_id', // foreignId
        'category_id', // foreignId
        'province_id', // foreignId
        'address_id', // foreignId hanel with delivery method refered to address table
        'product_id',
        'supplier_id',
    ];

    /**
     * Get the category that owns the purchase_requests.
     */
    public function category()
    {
        return $this->belongsTo(category::class, 'category_id');
    }

    /**
     * Get the user that owns the purchase_requests.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Get the user that owns the purchase_requests.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
    /**
     * Get the purchase_offer that owns the purchase_requests.
     */
    public function purchase_offer()
    {
        return $this->hasMany(purchase_offers::class, 'purchase_request_id');
    }
    public function purchase_offer_user(int $userId)
    {
        return $this->purchase_offer()->where('user_id', $userId)->first();
    }
    /**
     * Get the invoice that owns the purchase_requests.
     */
    public function invoice()
    {
        return $this->hasOne(invoice::class);
    }
    /**
     * Get the province that owns the purchase_requests.
     */
    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id'); // TODO: check Priority
    }

    /**
     * Get the address to delivery for user.
     */
    public function delivery()
    {
        return $this->hasMany(Address::class)->with('province:id,name');
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    /**
     * Get the product (it's come from shop request).
     */
    public function product()
    {
        return $this->belongsTo(product::class);
    }
    /**
     * Get the active purchase_requests that user owns.
     */
    public function active_purchase_requests()
    {
        return $this->where('status', 'active');
    }
    /**
     * Get the pending purchase_requests that user owns.
     */
    public function pending_purchase_requests()
    {
        return $this->where('status', 'pending');
    }

    /**
     * Semi boot function for encode/decode
     */
    // Mutator for the 'amount' attribute
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = json_encode($value);
    }

    // Mutator for the 'image' attribute
    public function setImageAttribute($value)
    {
        $this->attributes['image'] = json_encode($value);
    }

    // Accessor for the 'amount' attribute
    public function getAmountAttribute($value)
    {
        $res = json_decode($value, true);
        $res['name'] = __('message.' . $res['field']);
        return $res;
    }

    // Accessor for the 'image' attribute
    public function getImageAttribute($value)
    {
        // return json_decode($value, true);
        // Check if $value is null before decoding
        return $value === null ? null : json_decode($value, true);
    }

    /**
     * Scope a query to include only the specified fields from the category relationship.
     */
    public function scopeWithCategory($query)
    {
        return $query->with('category:id,title');
    }

    /**
     * Scope a query to include only the specified fields from the province relationship.
     */
    public function scopeWithProvince($query)
    {
        return $query->with('province:id,name');
    }
    /**
     * Scope a query to include only the specified fields from the delivery address relationship.
     */
    public function scopeWithDelivery($query)
    {
        return $query->with('addresses');
    }
    /**
     * Safe select prev, it's hide user_id also
     */
    public function scopeSelectSafePrev($query)
    {
        $columns = ['id', 'title' ,'active_time', 'amount', 'category_id', 'created_at', 'delivery_date', 'image', 'proposed_price', 'province_id', 'purchase_offer_id', 'status', 'product_id'];
        return $query->select($columns);
    }
    /**
     * Safe select with details, it's hide user_id also
     */
    public function scopeSelectSafeDetails($query)
    {
        $columns = ['id', 'title' ,'active_time', 'amount', 'category_id', 'created_at', 'delivery_date', 'image', 'proposed_price', 'province_id', 'purchase_offer_id', 'status', 'quality', 'features', 'details', 'product_id'];
        return $query->select($columns);
    }
}
