<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_characteristics extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'product_id',
        'category_id',
        'characteristic_id',
        'default_value',
    ];

    // Relationship with characteristics
    public function characteristics()
    {
        return $this->belongsTo(characteristics::class, 'characteristic_id', 'id');
    }
    // Relationship with product
    public function product()
    {
        return $this->belongsTo(product::class);
    }

    /**
     * Get the category that owns the product_characteristics.
     */
    public function category()
    {
        return $this->belongsTo(category::class);
    }
    /**
     * Get the category characteristics that owns the product_characteristics.
     */
    public function category_characteristics()
    {
        return $this->hasMany(category_characteristics::class, 'characteristic_id', 'id');
    }
}
