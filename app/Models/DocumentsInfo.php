<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentsInfo extends Model
{
    use HasFactory;

    protected $table = 'documents_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'national_card_image',
        'national_card_image_back',
    ];

    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
