<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
