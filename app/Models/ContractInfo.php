<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractInfo extends Model
{
    use HasFactory;

    protected $table = 'contract_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'contract_image',
        'start_date',
        'end_date',
    ];

    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
