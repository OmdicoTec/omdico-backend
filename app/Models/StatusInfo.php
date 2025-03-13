<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusInfo extends Model
{
    use HasFactory;

    protected $table = 'status_infos';
    /**
     * Fillable fields
     */
    protected $fillable = [
        'user_id',
        'table_name',
        'is_approved',
        'is_editable',
        'is_failed',
        'note',
        'data',
    ];
    /**
     * Belongs to relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
