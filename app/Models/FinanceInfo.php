<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceInfo extends Model
{
    use HasFactory;

    protected $table = 'finance_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'card_number',
        'shaba_number',
    ];

    /**
     * Get the user that owns the FinanceInfo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
