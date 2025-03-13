<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class category extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * user_id, is_actived, is_root, parent_id, title, other_title, details
     */
    protected $fillable = [
        'user_id',
        'is_actived',
        'is_root',
        'parent_id',
        'title',
        'other_title',
        'details',
        'slug',
        'description'
    ];

    /**
     * Safe select prev
     */
    public function scopeSelectSafePrev($query)
    {
        $columns = ['id', 'title', 'slug'];
        return $query->select($columns);
    }

    public function parent()
    {
        return $this->belongsTo(category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(category::class, 'parent_id');
    }
    /**
     * Returns the supplier products for the category.
     *
     * @return HasMany
     */
    public function supplier_products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    /**
     * Example:
     * category::find(1)->supplierList(['id','name','mobile_number'])->get()->makeHidden('pivot'); // pluck
     */
    public function supplierList($attr = ['id'])
    {
        $prefixedAttr = array_map(function($item) {
            return 'users.' . $item;
        }, $attr);
        return $this->belongsToMany(User::class, 'supplier_products')->select($prefixedAttr);
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function products()
    {
        return $this->hasMany(product::class);
    }

    public function keywords()
    {
        return $this->hasMany(Keyword::class, 'category_id')->select(['id', 'title', 'category_id']);
    }

    public function shopCategoryProducts()
    {
        return $this->hasMany(product::class, 'category_id')
            ->selectSafePrev()
            ->where('is_actived', true)
            ->where('is_approved', true)
            ->latest('created_at');
        // ->orderBy('created_at', 'desc');

    }
    // Relationship with characteristics model
    public function characteristics()
    {
        return $this->belongsToMany(characteristics::class, 'category_characteristics', 'category_id', 'characteristic_id');
    }

    /**
     * Get the purchases requests for the user.
     */
    public function purchase_requests()
    {
        return $this->hasMany(purchase_requests::class);
    }

    /**
     * Get the products characteristics for the category.
     */
    public function product_characteristics()
    {
        return $this->hasMany(product_characteristics::class);
    }
    /**
     * Get the category characteristics for the category.
     */
    public function category_characteristics()
    {
        return $this->hasMany(category_characteristics::class);
    }
    /**
     * This is a method docstring.
     *
     * This method is a scope method called "WithSupplierproduct" which is used to eager load the "supplier_products" relationship for the current model.
     *
     * @param $query The query builder instance.
     * @return $query The query builder instance with the "supplier_products" relationship eager loaded.
     */
    public function scopeWithSupplierproduct($query)
    {
        return $query->with('supplier_products:category_id,user_id');
    }
}
