<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'purchase_request_id',
        'purchase_offer_id',
        'buyer_id',
        'seller_id',
        'total_amount',
        'buyer_status',
        'seller_status',
        'is_seller_accepted',
        'commission_amount',
        'amount_owed_by_buyer',
        'total_buyer_deposit',
        'amount_owed_to_supplier',
        'total_supplier_deposit',
        'note',
    ];

    // Relationships

    /**
     * Get the purchase request that owns the invoice.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(purchase_requests::class);
    }

    /**
     * Get the purchase offer that owns the invoice.
     */
    public function purchaseOffer()
    {
        return $this->belongsTo(purchase_offers::class);
    }

    /**
     * Get the buyer that owns the invoice.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id','id');
    }
    /**
     * Get the seller that owns the invoice.
     */
    public function seller()
    {
        return $this->belongsTo(User::class,'seller_id','id');
    }

    /**
     * Get the transfers that owns the invoice.
     */
    public function transfers()
    {
        return $this->hasMany(transfers::class);
    }

}
