<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at', 'user_id'];

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    /**
     * Get the products that owns the Carts.
     */
    public function products()
    {
        return $this->belongsTo(product::class, 'product_id');
    }

    /**
     * Scope a query to include only the specified fields from the products relationship.
     */
    public function scopeWithProduct($query)
    {
        return $query->with('products:id,title,category_id,media,price,province_id,category_id,user_id');
        // return $query->with(['product' => function ($query) {
        //     $query->select('id', 'title', 'category_id', 'media', 'price');
        //     $query->with('category:id,name'); // Add this line to eager load the category relationship
        // }]);
    }
}
