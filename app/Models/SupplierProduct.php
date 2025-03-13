<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    // protected $hidden = ['id'];

    public $timestamps = false;
    // The attributes that are mass assignable.
    protected $fillable = ['user_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(category::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
