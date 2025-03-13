<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturalInfo extends Model
{
    use HasFactory;

    protected $table = 'natural_infos';
    /**
     * Fillable fields
     *
     * birth_date, gender are removed
    */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'national_code',
        'mobile_number',
        'other_mobile_number',
        'is_legal_person',
    ];

    /**
     * Convert is_legal_person boolean to true or false
     */
    public function getIsLegalPersonAttribute($value)
    {
        return $value ? true : false;
    }
    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
