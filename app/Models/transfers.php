<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transfers extends Model
{
    use HasFactory;


    // Fillable fields
    protected $fillable = [
        'user_id',
        'invoice_id',
        'type',
        'from',
        'bank_status',
        'bank_message',
        'amount',
        'verify_at',
        'tracking_code',
        'uuid',
        'transaction_id',
        'provider',
        'is_shaparak',
        'url',
        'card_number',
    ];

    // Relationships
    /**
     * Get the user that owns the transfers.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    /**
     * Get the invoice that owns the transfers.
     */
    public function invoice()
    {
        return $this->belongsTo(invoice::class, 'invoice_id','id');
    }
}
