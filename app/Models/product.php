<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    // table name
    protected $table = 'products';

    // fillable fields
    protected $fillable = [
        'user_id',
        'title',
        'category_id', // foreign key to categories table, not yet implemented
        'category', // name of category

        // features of product json format, color, type, size, garantee is default
        // '{"color": "", "type": "", "size": "", "garantee": ""}'
        'features',
        'details', // details of product in text format

        // media of product json format, images and videos is default
        // '{"images": [], "videos": []}'
         // media of product, images and videos
        'media',
        'limited', // product inventory (stock)
        'quantity', // quantity of product in stock if product is limited

        // time of product is available per each product quantity, json format
        // '{"quantity": "", "time": ""}'
        'time',
        'commission',
        'price',
        'is_actived',
        'is_approved',
        'is_rejected',
        'comment',
        'new_data',
        'province_id',
    ];

    // // casts
    // protected $casts = [
    //     'features' => 'array',
    //     'media' => 'array',
    //     'time' => 'array',
    //     'new_data' => 'array',
    // ];

    protected static function boot()
    {
        parent::boot();

        // Registering a deleting event to handle the cascade deletion
        static::deleting(function ($characteristic) {
            // Corrected: Delete related records in product_characteristics pivot table directly
            $characteristic->product_characteristics()->delete();

        });
    }
    // covert json to array for features & media
    public function getFeaturesAttribute($value)
    {
        return json_decode($value);
    }
    public function getMediaAttribute($value)
    {
        return json_decode($value);
    }
    // convert limited to false and true
    public function getLimitedAttribute($value)
    {
        return $value == 1 ? true : false;
    }
    // convert is_actived to false and true
    public function getIsActivedAttribute($value)
    {
        return $value == 1 ? true : false;
    }
    // convert is_approved to false and true
    public function getIsApprovedAttribute($value)
    {
        return $value == 1 ? true : false;
    }
    // convert is_rejected to false and true
    public function getIsRejectedAttribute($value)
    {
        return $value == 1 ? true : false;
    }
    // relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(category::class, 'category_id');
    }
    // Relationship with product_characteristics
    public function product_characteristics()
    {
        return $this->hasMany(product_characteristics::class, 'product_id');
    }

    public function keywords()
    {
        return $this->hasMany(Keyword::class, 'product_id')->select(['id', 'title', 'product_id']);
    }
    public function productmetaseos()
    {
        return $this->hasOne(Productmetaseo::class, 'product_id');
    }
    /**
     * Safe select prev
     */
    public function scopeSelectSafePrev($query)
    {
        $columns = ['id','title','category_id','media','price','province_id'];
        return $query->select($columns);
    }

    /**
     * Safe select for show product in shop with details
     */
    public function scopeSelectSafeShopDetails($query)
    {
        $columns = ['id','user_id','title','category_id','features','details','media','limited','time','price'];
        return $query->select($columns);
    }
}
