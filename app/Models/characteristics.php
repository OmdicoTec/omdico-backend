<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class characteristics extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];
    // Fillable
    protected $fillable = [
        'name',
        'slug',
        'input_type',
        'is_constant',
        'values'
    ];

    protected static function boot()
    {
        parent::boot();

        // Registering a deleting event to handle the cascade deletion
        static::deleting(function ($characteristic) {
            // Corrected: Delete related records in category_characteristics pivot table directly
            $characteristic->category_characteristics()->delete();

            // Corrected: Delete related records in product_characteristics table directly
            $characteristic->product_characteristics()->delete();
        });
    }
    // Relationship with category_characteristics model
    public function category_characteristics()
    {
        return $this->hasMany(category_characteristics::class, 'characteristic_id');
    }

    // Relationship with product_characteristics model
    public function product_characteristics()
    {
        return $this->hasMany(product_characteristics::class, 'characteristic_id');
    }

    public function getValuesAttribute($value)
    {
        return json_decode($value);
    }

    public function categories()
    {
        return $this->belongsToMany(category::class, 'category_characteristics', 'characteristic_id', 'category_id');
    }
}
