<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalInfo extends Model
{
    use HasFactory;

    protected $table = 'legal_infos';
    /**
     * Fillable fields
     */

    protected $fillable = [
        'user_id',
        'company_name',
        'company_type',
        'registration_number',
        'national_code',
        'economic_code',
        'signatory',
        'store_name',
    ];

    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
