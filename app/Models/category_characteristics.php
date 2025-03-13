<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category_characteristics extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'category_id',
        'characteristic_id',
        'default_value',
    ];

    protected static function boot()
    {
        parent::boot();

        // Registering an updating event to handle the removal of related product_characteristics
        static::updating(function ($categoryCharacteristic) {
            // Get the old category_id value before the update
            $oldCategoryId = $categoryCharacteristic->getOriginal('category_id');

            // Check if the category_id has changed
            if ($oldCategoryId !== $categoryCharacteristic->category_id) {
                // Remove related product_characteristics with the old category_id
                $categoryCharacteristic->product_characteristics()->where('category_id', $oldCategoryId)->delete();
            }
        });
    }
    // Relationship with characteristics
    public function characteristics()
    {
        return $this->belongsTo(characteristics::class, 'characteristic_id', 'id');
    }
    // Relationship with category
    public function category()
    {
        return $this->belongsTo(category::class);
    }

    public function product_characteristics()
    {
        return $this->hasMany(product_characteristics::class, 'characteristic_id');
    }
}
